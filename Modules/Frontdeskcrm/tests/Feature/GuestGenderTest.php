<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Modules\Frontdeskcrm\Support\GuestGender;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * guests is the databank and registrations is the process record that snapshots
 * a guest's simple attributes, so the same three values are written to both.
 * They were not written the same way: the directory and the importer wrote
 * Title case, the public booking form wrote lowercase.
 *
 * `registrations.gender` is an enum, and MySQL compares enum members
 * case-insensitively, so nothing ever rejected the difference - it was silently
 * coerced. These tests pin the single vocabulary down, because the failure mode
 * it caused was a wrong name on a guest's paperwork rather than an error.
 */
class GuestGenderTest extends TestCase
{
    #[DataProvider('spellingProvider')]
    public function test_any_spelling_reduces_to_one_canonical_value(mixed $input, ?string $expected): void
    {
        $this->assertSame($expected, GuestGender::normalise($input));
    }

    /**
     * @return array<string, array{0: mixed, 1: ?string}>
     */
    public static function spellingProvider(): array
    {
        return [
            // What the public booking form writes.
            'lowercase male' => ['male', GuestGender::MALE],
            'lowercase female' => ['female', GuestGender::FEMALE],
            'lowercase other' => ['other', GuestGender::OTHER],

            // What the directory and the importer write.
            'title case male' => ['Male', GuestGender::MALE],
            'title case female' => ['Female', GuestGender::FEMALE],
            'title case other' => ['Other', GuestGender::OTHER],

            // What turns up in spreadsheets.
            'uppercase' => ['MALE', GuestGender::MALE],
            'mixed case' => ['fEmAlE', GuestGender::FEMALE],
            'surrounding space' => ['  Female  ', GuestGender::FEMALE],
            'single letter m' => ['m', GuestGender::MALE],
            'single letter f' => ['F', GuestGender::FEMALE],

            // A cell containing something else entirely.
            'nonsense becomes other' => ['purple', GuestGender::OTHER],
            'numeric becomes other' => [1, GuestGender::OTHER],

            // Nothing stated stays nothing stated, rather than inventing Other.
            'blank' => ['', null],
            'whitespace' => ['   ', null],
            'null' => [null, null],
        ];
    }

    public function test_a_blank_value_does_not_become_other(): void
    {
        // The guest may simply not have stated a gender. Recording that as
        // "Other" would be a false statement about the guest, and would make
        // them appear in the directory's Other filter.
        $this->assertNull(GuestGender::normalise(''));
        $this->assertNull(GuestGender::normalise(null));
        $this->assertNull(GuestGender::normalise('  '));
    }

    /**
     * @return array<string, array{0: mixed, 1: ?string}>
     */
    public static function honorificProvider(): array
    {
        return [
            // The bug: a lowercase value used to fail an `== 'Male'` test and
            // take the "Ms." branch, so a male guest got the wrong title.
            'lowercase male is Mr' => ['male', 'Mr.'],
            'title case male is Mr' => ['Male', 'Mr.'],
            'lowercase female is Ms' => ['female', 'Ms.'],
            'title case female is Ms' => ['Female', 'Ms.'],

            // No guess: "Other" and unstated stay blank rather than defaulting
            // to "Ms.", which is what put the wrong name on paperwork before.
            'other has no honorific' => ['other', null],
            'title case other has none' => ['Other', null],
            'null has no honorific' => [null, null],
            'blank has no honorific' => ['', null],
        ];
    }

    #[DataProvider('honorificProvider')]
    public function test_the_honorific_never_guesses(mixed $gender, ?string $expected): void
    {
        $this->assertSame($expected, GuestGender::honorific($gender));
    }

    public function test_every_canonical_value_round_trips(): void
    {
        foreach (GuestGender::options() as $value => $label) {
            $this->assertSame($value, $label, 'Option label should equal its value.');
            $this->assertSame($value, GuestGender::normalise($value));
        }
    }

    public function test_the_registration_values_are_the_enum_members(): void
    {
        // registrations.gender is enum('male','female','other'). forRegistration
        // must hand back something that column will hold, or MySQL coerces it
        // behind the application's back - which is how the two tables drifted.
        $this->assertSame(['male', 'female', 'other'], GuestGender::registrationValues());

        foreach (GuestGender::options() as $canonical => $_) {
            $this->assertContains(
                GuestGender::forRegistration($canonical),
                GuestGender::registrationValues(),
                "{$canonical} must map onto an enum member.",
            );
        }
    }

    public function test_for_registration_returns_null_when_nothing_is_stated(): void
    {
        // A nullable column must receive null, not the empty string, or the
        // insert is rejected by the enum's strict mode.
        $this->assertNull(GuestGender::forRegistration(null));
        $this->assertNull(GuestGender::forRegistration(''));
    }

    public function test_matches_ignores_casing_in_both_directions(): void
    {
        // Used to decide whether a resubmitted value has actually changed.
        $this->assertTrue(GuestGender::matches('Male', 'male'));
        $this->assertTrue(GuestGender::matches('male', 'Male'));
        $this->assertTrue(GuestGender::matches('Female', 'female'));
        $this->assertTrue(GuestGender::matches('Other', 'OTHER'));

        $this->assertFalse(GuestGender::matches('Male', 'female'));
        $this->assertFalse(GuestGender::matches('Male', null));
        $this->assertFalse(GuestGender::matches(null, 'Male'));
    }

    public function test_matches_does_not_treat_unstated_as_other(): void
    {
        // Otherwise clearing the field would read as a change to "Other".
        $this->assertFalse(GuestGender::matches(null, ''));
        $this->assertFalse(GuestGender::matches('', 'Other'));
    }
}
