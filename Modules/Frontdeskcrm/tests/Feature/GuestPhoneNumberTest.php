<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Modules\Frontdeskcrm\Support\GuestPhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The phone spellings a guest may already be filed under.
 *
 * `guests.contact_number` is UNIQUE, so anything narrower than the real set of
 * spellings lets the same guest be inserted twice and then blows up on the
 * index. Anything wider risks calling two different people the same guest, so
 * these also pin down what must NOT be treated as a match.
 */
class GuestPhoneNumberTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: array<int, string>}>
     */
    public static function spellingProvider(): array
    {
        return [
            'local, already clean' => [
                '08011112222',
                ['08011112222', '+2348011112222', '2348011112222'],
            ],
            'local, spaced as typed by a human' => [
                '0801 111 2222',
                ['08011112222', '+2348011112222', '2348011112222'],
            ],
            'local, dashed' => [
                '0801-111-2222',
                ['08011112222', '+2348011112222', '2348011112222'],
            ],
            'local, bracketed area code' => [
                '(0801) 111.2222',
                ['08011112222', '+2348011112222', '2348011112222'],
            ],
            'international, already clean' => [
                '+2348011112222',
                ['+2348011112222', '2348011112222', '08011112222'],
            ],
            'international, spaced' => [
                '+234 801 111 2222',
                ['+2348011112222', '2348011112222', '08011112222'],
            ],
            // The plus is not part of any unique index, so both must be offered.
            'international, plus stripped off' => [
                '234 801 111 2222',
                ['2348011112222', '+2348011112222', '08011112222'],
            ],
            'international without the plus' => [
                '2348011112222',
                ['2348011112222', '+2348011112222', '08011112222'],
            ],
            'leading and trailing whitespace' => [
                "  08011112222\n",
                ['08011112222', '+2348011112222', '2348011112222'],
            ],
        ];
    }

    /**
     * @param  array<int, string>  $expected
     */
    #[DataProvider('spellingProvider')]
    public function test_it_offers_every_spelling_the_number_could_be_stored_as(string $input, array $expected): void
    {
        $this->assertSame($expected, GuestPhoneNumber::spellings($input));
    }

    public function test_the_original_input_is_always_included(): void
    {
        // A lookup can only ever be widened by this list, never narrowed: if the
        // stored value is exactly what the user typed, it must still match.
        foreach (['08011112222', '+2348011112222', '2348011112222', '555-0199', '+1 555 0199'] as $input) {
            $this->assertContains(
                GuestPhoneNumber::clean($input),
                GuestPhoneNumber::spellings($input),
                "'{$input}' is not matched against its own cleaned form."
            );
        }
    }

    public function test_two_formatings_of_one_number_match(): void
    {
        $this->assertTrue(GuestPhoneNumber::matches('08011112222', '0801 111 2222'));
        $this->assertTrue(GuestPhoneNumber::matches('08011112222', '0801-111-2222'));
        $this->assertTrue(GuestPhoneNumber::matches('+2348011112222', '+234 801 111 2222'));
        $this->assertTrue(GuestPhoneNumber::matches('2348011112222', '234.801.111.2222'));
    }

    public function test_different_numbers_do_not_match(): void
    {
        // The whole cost of getting this wrong: a real guest turned away from
        // their own profile because a digit happened to be typed differently.
        $this->assertFalse(GuestPhoneNumber::matches('08011112222', '08011112223'));
        $this->assertFalse(GuestPhoneNumber::matches('08011112222', '0801111222'));
        $this->assertFalse(GuestPhoneNumber::matches('08011112222', '080111122200'));
    }

    public function test_a_missing_number_never_matches_anything(): void
    {
        // Otherwise every blank contact_number in the table would be reported as
        // a match, since "" has the same digits as every other blank.
        $this->assertFalse(GuestPhoneNumber::matches('', ''));
        $this->assertFalse(GuestPhoneNumber::matches(null, '08011112222'));
        $this->assertFalse(GuestPhoneNumber::matches('08011112222', null));
        $this->assertSame([], GuestPhoneNumber::spellings(null));
        $this->assertSame([], GuestPhoneNumber::spellings(''));
        $this->assertSame([], GuestPhoneNumber::spellings('   '));
    }

    public function test_a_truncated_number_cannot_generate_a_local_spelling(): void
    {
        // "+234" on its own is a country code with no subscriber number. It is
        // harmless to look up, but it must not be expanded into "080..." - that
        // would match some unrelated guest.
        $this->assertNotContains('0', GuestPhoneNumber::spellings('+234'));
        $this->assertNotContains('080', GuestPhoneNumber::spellings('+234801'));
    }

    public function test_a_non_nigerian_number_is_not_rewritten(): void
    {
        // The local/international conversion is Nigerian-specific. Guessing for
        // other countries would invent matches that do not exist.
        $this->assertSame(['+15550199', '15550199'], GuestPhoneNumber::spellings('+1 555 0199'));
        $this->assertSame(['5550199'], GuestPhoneNumber::spellings('555-0199'));
    }
}
