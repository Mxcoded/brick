<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Modules\Frontdeskcrm\Support\GuestNationality;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The contract for the nationality vocabulary.
 *
 * The column held three kinds of value at once: a country name ("Nigeria"), a
 * demonym ("Nigerian") and an abbreviation ("UK"). Because the profile form
 * offered a short `<select>` and compared with a strict `===`, a guest recorded
 * as "Nigerian" matched no option, rendered blank, and was overwritten with an
 * empty string the next time they saved their profile.
 */
class GuestNationalityTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function spellings(): array
    {
        return [
            'country name' => ['Nigeria', 'Nigeria'],
            'demonym' => ['Nigerian', 'Nigeria'],
            'demonym plural' => ['Nigerians', 'Nigeria'],
            'long official name' => ['Federal Republic of Nigeria', 'Nigeria'],
            'UK name' => ['United Kingdom', 'United Kingdom'],
            'UK abbreviation' => ['UK', 'United Kingdom'],
            'UK demonym' => ['British', 'United Kingdom'],
            'GB spelling of abbreviation' => ['U.K.', 'United Kingdom'],
            'full official UK name' => ['United Kingdom of Great Britain and Northern Ireland', 'United Kingdom'],
            'England' => ['England', 'United Kingdom'],
            'Scottish' => ['Scottish', 'United Kingdom'],
            'Welsh' => ['Welsh', 'United Kingdom'],
            'US name' => ['United States', 'United States'],
            'US abbreviation' => ['USA', 'United States'],
            'US single letter' => ['US', 'United States'],
            'US demonym' => ['American', 'United States'],
            'full official US name' => ['United States of America', 'United States'],
            'Ghanaian' => ['Ghanaian', 'Ghana'],
            'South African' => ['South African', 'South Africa'],
            'lowercase country' => ['nigeria', 'Nigeria'],
            'surrounding whitespace' => ['  Nigerian  ', 'Nigeria'],
        ];
    }

    #[Test]
    #[DataProvider('spellings')]
    public function it_folds_any_spelling_onto_the_country_name(string $input, string $expected): void
    {
        $this->assertSame($expected, GuestNationality::normalise($input));
    }

    #[Test]
    public function a_blank_nationality_is_null(): void
    {
        $this->assertNull(GuestNationality::normalise(null));
        $this->assertNull(GuestNationality::normalise(''));
        $this->assertNull(GuestNationality::normalise('   '));
    }

    #[Test]
    public function an_unrecognised_country_is_preserved_rather_than_discarded(): void
    {
        // The deliberate opposite of the identification-type behaviour: a country
        // this table has never seen is still a real answer, and blanking it would
        // lose information the guest supplied.
        $this->assertSame('Curaçao', GuestNationality::normalise('Curaçao'));
        $this->assertSame('Atlantis', GuestNationality::normalise('Atlantis'));
    }

    #[Test]
    public function an_unrecognised_country_is_still_trimmed(): void
    {
        $this->assertSame('Atlantis', GuestNationality::normalise('  Atlantis  '));
    }

    #[Test]
    public function it_treats_a_country_and_its_demonym_as_the_same_nationality(): void
    {
        $this->assertTrue(GuestNationality::matches('Nigerian', 'Nigeria'));
        $this->assertTrue(GuestNationality::matches('Nigeria', 'Nigerian'));
        $this->assertTrue(GuestNationality::matches('UK', 'British'));
    }

    #[Test]
    public function two_different_countries_do_not_match(): void
    {
        $this->assertFalse(GuestNationality::matches('Nigeria', 'Ghana'));
        $this->assertFalse(GuestNationality::matches('United Kingdom', 'United States'));
    }

    #[Test]
    public function a_blank_value_matches_nothing(): void
    {
        // Otherwise every blank would match every other blank, and a select
        // using matches() would mark the wrong option selected.
        $this->assertFalse(GuestNationality::matches('', ''));
        $this->assertFalse(GuestNationality::matches(null, 'Nigeria'));
    }
}
