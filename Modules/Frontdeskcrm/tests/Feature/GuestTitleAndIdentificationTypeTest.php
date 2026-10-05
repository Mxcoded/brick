<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Modules\Frontdeskcrm\Support\GuestIdentificationType;
use Modules\Frontdeskcrm\Support\GuestTitle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The contract for the title and identification-type vocabularies.
 *
 * Each class documents a bug that was silent: two surfaces disagreed on the
 * spelling of the same value, and because both offered a `<select>`, a value
 * stored by one rendered blank on the other and was then overwritten with an
 * empty string on save.
 */
class GuestTitleAndIdentificationTypeTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function titleSpellings(): array
    {
        return [
            'Mr no period' => ['Mr', GuestTitle::MR],
            'Mister spelled out' => ['Mister', GuestTitle::MR],
            'Mrs no period' => ['Mrs', GuestTitle::MRS],
            'Mrs doubled s spelled out' => ['Missus', GuestTitle::MRS],
            'Ms no period' => ['Ms', GuestTitle::MS],
            'Miss' => ['Miss', GuestTitle::MISS],
            'Dr no period' => ['Dr', GuestTitle::DR],
            'Doctor spelled out' => ['Doctor', GuestTitle::DR],
            'Prof no period' => ['Prof', GuestTitle::PROF],
            'Professor spelled out' => ['Professor', GuestTitle::PROF],
            'Chief' => ['Chief', GuestTitle::CHIEF],
            'already canonical' => ['Mrs.', GuestTitle::MRS],
            'lowercase' => ['mrs.', GuestTitle::MRS],
            'padded' => ['  Dr.  ', GuestTitle::DR],
        ];
    }

    #[Test]
    #[DataProvider('titleSpellings')]
    public function it_folds_any_title_spelling_onto_the_canonical_value(string $input, string $expected): void
    {
        $this->assertSame($expected, GuestTitle::normalise($input));
    }

    #[Test]
    public function every_canonical_title_survives_its_own_normalisation(): void
    {
        foreach (GuestTitle::values() as $canonical) {
            $this->assertSame($canonical, GuestTitle::normalise($canonical), $canonical);
        }
    }

    #[Test]
    public function a_blank_title_is_null_rather_than_an_empty_string(): void
    {
        // The bug this guards: an empty string stored over a good title is
        // invisible until the guest notices their honorific has gone.
        $this->assertNull(GuestTitle::normalise(null));
        $this->assertNull(GuestTitle::normalise(''));
        $this->assertNull(GuestTitle::normalise('   '));
    }

    #[Test]
    public function an_unrecognised_title_is_null_because_the_column_is_nullable(): void
    {
        $this->assertNull(GuestTitle::normalise('Honorable'));
    }

    #[Test]
    public function it_keeps_a_stored_title_instead_of_deriving_one_from_gender(): void
    {
        // Deriving from gender alone downgraded a married woman to "Ms." on
        // every registration.
        $this->assertSame(GuestTitle::MRS, GuestTitle::forGuest('Mrs', 'female'));
        $this->assertSame(GuestTitle::DR, GuestTitle::forGuest('Dr.', 'female'));
    }

    #[Test]
    public function it_falls_back_to_the_gendered_honorific_when_no_title_is_on_file(): void
    {
        $this->assertSame(GuestTitle::MR, GuestTitle::forGuest(null, 'male'));
        $this->assertSame(GuestTitle::MS, GuestTitle::forGuest(null, 'female'));
    }

    #[Test]
    public function a_stored_title_beats_gender_even_when_it_needs_folding(): void
    {
        $this->assertSame(GuestTitle::PROF, GuestTitle::forGuest('Prof', 'male'));
    }

    #[Test]
    public function it_treats_two_spellings_of_one_title_as_the_same_value(): void
    {
        $this->assertTrue(GuestTitle::matches('Mrs', 'Mrs.'));
        $this->assertFalse(GuestTitle::matches('Mrs.', 'Ms.'));
        $this->assertFalse(GuestTitle::matches('', 'Mr.'));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function identificationSpellings(): array
    {
        return [
            'passport short' => ['Passport', GuestIdentificationType::PASSPORT],
            'passport long' => ['International Passport', GuestIdentificationType::PASSPORT],
            'passport abbreviated' => ['Intl Passport', GuestIdentificationType::PASSPORT],
            'NIN short' => ['NIN', GuestIdentificationType::NIN],
            'NIN long' => ['National ID (NIN)', GuestIdentificationType::NIN],
            'NIN spelled out' => ['National Identification Number', GuestIdentificationType::NIN],
            'licence with apostrophe' => ["Driver's License", GuestIdentificationType::DRIVERS_LICENCE],
            'license without apostrophe' => ['Drivers License', GuestIdentificationType::DRIVERS_LICENCE],
            'british spelling' => ["Driver's Licence", GuestIdentificationType::DRIVERS_LICENCE],
            'voters card no apostrophe' => ['Voters Card', GuestIdentificationType::VOTERS_CARD],
            'voters card with apostrophe' => ["Voter's Card", GuestIdentificationType::VOTERS_CARD],
            'voter' => ['Voter', GuestIdentificationType::VOTERS_CARD],
            'BVN short' => ['BVN', GuestIdentificationType::BVN],
            'BVN long' => ['Bank Verification Number (BVN)', GuestIdentificationType::BVN],
            'other' => ['Other', GuestIdentificationType::OTHER],
            'other govt id' => ['Other Govt ID', GuestIdentificationType::OTHER],
            'already canonical' => ['Voter\'s Card', GuestIdentificationType::VOTERS_CARD],
            'padded' => ['  NIN  ', GuestIdentificationType::NIN],
        ];
    }

    #[Test]
    #[DataProvider('identificationSpellings')]
    public function it_folds_any_identification_spelling_onto_the_canonical_value(string $input, string $expected): void
    {
        $this->assertSame($expected, GuestIdentificationType::normalise($input));
    }

    #[Test]
    public function every_canonical_identification_type_survives_its_own_normalisation(): void
    {
        foreach (GuestIdentificationType::values() as $canonical) {
            $this->assertSame($canonical, GuestIdentificationType::normalise($canonical), $canonical);
        }
    }

    #[Test]
    public function a_blank_identification_type_is_null(): void
    {
        $this->assertNull(GuestIdentificationType::normalise(null));
        $this->assertNull(GuestIdentificationType::normalise(''));
        $this->assertNull(GuestIdentificationType::normalise('  '));
    }

    #[Test]
    public function an_unrecognised_document_is_left_for_a_human_rather_than_filed_as_other(): void
    {
        // A guest's ID is a legal identity document. Recording a passport this
        // code has no name for as "Other" would misdescribe what they hold.
        $this->assertNull(GuestIdentificationType::normalise('Residence Permit'));
        $this->assertNull(GuestIdentificationType::normalise('WAEC Card'));
    }

    #[Test]
    public function the_nin_format_is_enforced_however_the_type_was_spelled(): void
    {
        // The bug: the number check compared against raw literals, so the long
        // 'National ID (NIN)' spelling offered by the walk-in and profile forms
        // skipped the 11-digit requirement entirely.
        foreach (['NIN', 'National ID (NIN)', 'national identification number'] as $spelling) {
            $this->assertNotNull(GuestIdentificationType::numberRule($spelling), $spelling);
            $this->assertTrue(GuestIdentificationType::numberIsValid($spelling, '12345678901'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, '12345'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, '123456789012'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, '12345A78901'), $spelling);
        }
    }

    #[Test]
    public function the_passport_format_is_enforced_however_the_type_was_spelled(): void
    {
        foreach (['Passport', 'International Passport', 'intl passport'] as $spelling) {
            $this->assertNotNull(GuestIdentificationType::numberRule($spelling), $spelling);
            $this->assertTrue(GuestIdentificationType::numberIsValid($spelling, 'A01234567'), $spelling);
            $this->assertTrue(GuestIdentificationType::numberIsValid($spelling, 'A012345678'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, '01234567'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, 'A012345'), $spelling);
            // Ten digits: one past the documented 7-9.
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, 'A0123456789'), $spelling);
        }
    }

    #[Test]
    public function the_drivers_licence_format_is_enforced_however_the_type_was_spelled(): void
    {
        // The bug: "Driver's License" bypassed the 3+12+1 check for the same reason.
        foreach (["Driver's License", 'Drivers License', "Driver's Licence", 'DL'] as $spelling) {
            $this->assertNotNull(GuestIdentificationType::numberRule($spelling), $spelling);
            $this->assertTrue(GuestIdentificationType::numberIsValid($spelling, 'ABC123456789012X'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, 'ABC12345678901X'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, 'AB123456789012X'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, 'ABC123456789012'), $spelling);
        }
    }

    #[Test]
    public function the_voters_card_format_is_enforced_however_the_type_was_spelled(): void
    {
        foreach (["Voter's Card", 'Voters Card', 'Voter'] as $spelling) {
            $this->assertNotNull(GuestIdentificationType::numberRule($spelling), $spelling);
            $this->assertTrue(GuestIdentificationType::numberIsValid($spelling, '1234567890123456789'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, '123456789012345678'), $spelling);
            $this->assertFalse(GuestIdentificationType::numberIsValid($spelling, '12345678901234567890'), $spelling);
        }
    }

    #[Test]
    public function bvn_and_other_impose_no_format_because_they_are_not_identity_documents(): void
    {
        foreach (['BVN', 'Bank Verification Number (BVN)', 'Other', 'Other Govt ID'] as $spelling) {
            $this->assertNull(GuestIdentificationType::numberRule($spelling), $spelling);
            $this->assertTrue(GuestIdentificationType::numberIsValid($spelling, 'anything at all'), $spelling);
        }
    }

    #[Test]
    public function no_type_accepts_a_blank_number(): void
    {
        foreach (GuestIdentificationType::values() as $type) {
            $this->assertFalse(GuestIdentificationType::numberIsValid($type, ''), $type);
            $this->assertFalse(GuestIdentificationType::numberIsValid($type, '   '), $type);
        }
    }

    #[Test]
    public function every_type_offered_by_the_selects_is_one_the_number_check_recognises(): void
    {
        // A type in options() with no branch in numberIsValid() would silently
        // accept any number, reintroducing the bypass this class exists to close.
        foreach (GuestIdentificationType::options() as $value => $label) {
            $this->assertSame(
                $value,
                GuestIdentificationType::normalise($value),
                "option {$label} does not normalise to itself"
            );
        }
    }

    #[Test]
    public function every_accepted_spelling_is_actually_foldable(): void
    {
        foreach (GuestIdentificationType::acceptedValues() as $spelling) {
            $this->assertNotNull(
                GuestIdentificationType::normalise($spelling),
                "'{$spelling}' is accepted on input but normalises to null"
            );
        }
    }

    #[Test]
    public function every_accepted_title_is_actually_foldable(): void
    {
        foreach (GuestTitle::acceptedValues() as $spelling) {
            $this->assertNotNull(
                GuestTitle::normalise($spelling),
                "'{$spelling}' is accepted on input but normalises to null"
            );
        }
    }
}
