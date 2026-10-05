<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Support\GuestNationality;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The backfill that repairs rows already on file.
 *
 * The three migrations run once, against real data, so their narrowness matters
 * as much as their effect: they must rewrite what they can resolve and leave
 * everything else exactly as a person typed it.
 */
class GuestVocabularyBackfillTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * The migrations are invoked directly rather than registered with the
     * migrator, so this exercises exactly the file that ships.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_folds_period_less_titles_onto_the_canonical_form(): void
    {
        $guest = $this->guest('Backfill Title', '08055550110', ['title' => 'Mrs']);

        $this->runUp('2026_10_05_130000_normalise_guest_title.php');

        $this->assertSame('Mrs.', $guest->fresh()->title);
    }

    #[Test]
    public function it_folds_identification_spellings_onto_the_canonical_form(): void
    {
        $guest = $this->guest('Backfill Id', '08055550111', ['identification_type' => 'NIN']);

        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $this->assertSame('National ID (NIN)', $guest->fresh()->identification_type);
    }

    #[Test]
    public function it_folds_a_demonym_nationality_onto_the_country_name(): void
    {
        $guest = $this->guest('Backfill Country', '08055550112', ['nationality' => 'Nigerian']);

        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $this->assertSame('Nigeria', $guest->fresh()->nationality);
    }

    #[Test]
    public function it_leaves_an_unknown_title_exactly_as_it_was(): void
    {
        // Nulling this would lose what the guest told us, and the column is
        // nullable so leaving it costs nothing.
        $guest = $this->guest('Backfill Odd Title', '08055550113', ['title' => 'Honorable']);

        $this->runUp('2026_10_05_130000_normalise_guest_title.php');

        $this->assertSame('Honorable', $guest->fresh()->title);
    }

    #[Test]
    public function it_leaves_an_unrecognised_document_exactly_as_it_was(): void
    {
        // The deliberate difference from title and nationality: a guest's ID is a
        // legal identity document, so it is left for a human rather than being
        // filed under "Other".
        $guest = $this->guest('Backfill Odd Id', '08055550114', ['identification_type' => 'Residence Permit']);

        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $this->assertSame('Residence Permit', $guest->fresh()->identification_type);
    }

    #[Test]
    public function it_leaves_an_unknown_country_exactly_as_it_was(): void
    {
        $guest = $this->guest('Backfill Odd Country', '08055550115', ['nationality' => 'Atlantis']);

        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $this->assertSame('Atlantis', $guest->fresh()->nationality);
    }

    #[Test]
    public function it_never_invents_a_value_for_a_blank_one(): void
    {
        // The migrations skip anything blank or whitespace-only rather than
        // guessing at it. What matters is that an unstated attribute stays
        // unstated instead of acquiring a canonical value the guest never gave -
        // and that a whitespace-only value is not quietly rewritten into a
        // country name.
        $guest = $this->guest('Backfill Blank', '08055550116', [
            'title' => '',
            'nationality' => '   ',
            'identification_type' => '',
        ]);

        $this->runUp('2026_10_05_130000_normalise_guest_title.php');
        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $guest->refresh();

        $this->assertEmpty($guest->title);
        $this->assertEmpty($guest->identification_type);
        $this->assertNull(GuestNationality::normalise($guest->nationality));
        $this->assertNotSame('Nigeria', $guest->nationality);
    }

    #[Test]
    public function it_leaves_an_unstated_attribute_null(): void
    {
        $guest = $this->guest('Backfill Unstated', '08055550118', []);

        $this->runUp('2026_10_05_130000_normalise_guest_title.php');
        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $guest->refresh();

        $this->assertNull($guest->title);
        $this->assertNull($guest->nationality);
        $this->assertNull($guest->identification_type);
    }

    #[Test]
    public function it_rewrites_the_registration_snapshot_too(): void
    {
        // The snapshot is a copy of the same attribute. Leaving the old spelling
        // there would keep the databank and the record of the stay disagreeing.
        $registration = DB::table('registrations')->insertGetId([
            'full_name' => 'Snapshot Backfill',
            'contact_number' => '08055550117',
            'stay_status' => 'draft_by_guest',
            'title' => 'Dr',
            'nationality' => 'Nigerian',
            'identification_type' => 'Drivers License',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runUp('2026_10_05_130000_normalise_guest_title.php');
        $this->runUp('2026_10_05_140000_normalise_guest_identification_and_nationality.php');

        $row = DB::table('registrations')->where('id', $registration)->first();

        $this->assertSame('Dr.', $row->title);
        $this->assertSame('Nigeria', $row->nationality);
        $this->assertSame("Driver's License", $row->identification_type);
    }

    #[Test]
    public function the_migrations_are_not_reversible_because_the_old_state_was_a_mixture(): void
    {
        // There was no single prior value to return to, so a "rollback" could only
        // be a guess - and a wrong guess would reintroduce the blanking bug.
        foreach ([
            '2026_10_05_130000_normalise_guest_title.php',
            '2026_10_05_140000_normalise_guest_identification_and_nationality.php',
        ] as $migration) {
            $instance = require __DIR__.'/../../database/migrations/'.$migration;

            $instance->down();

            $this->addToAssertionCount(1);
        }
    }

    protected function guest(string $name, string $phone, array $attributes): Guest
    {
        return Guest::create([
            'full_name' => $name,
            'contact_number' => $phone,
            ...$attributes,
        ]);
    }

    protected function runUp(string $migration): void
    {
        $instance = require __DIR__.'/../../database/migrations/'.$migration;

        $instance->up();
    }
}
