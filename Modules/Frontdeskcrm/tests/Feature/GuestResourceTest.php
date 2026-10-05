<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Modules\Frontdeskcrm\Filament\Pages\ImportGuests;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages\CreateGuest;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages\EditGuest;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages\ListGuests;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Pages\ViewGuest;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\RelationManagers\RegistrationsRelationManager;
use Modules\Frontdeskcrm\Filament\Resources\GuestResource\Widgets\GuestStatsOverview;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Tests\Concerns\GrantsFrontdeskRoles;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The guest directory as Filament actually renders it.
 *
 * Most of this exists because the previous version asked for things the schema
 * does not have. `first_name` and `last_name` are not columns on `guests` - the
 * table has one `full_name` - and the legacy guest card summed
 * registrations.total_nights, which is not a column either; the field is
 * `no_of_nights`. Both surfaced as errors in front of a user rather than as
 * anything a test noticed.
 */
class GuestResourceTest extends TestCase
{
    use DatabaseTransactions;
    use GrantsFrontdeskRoles;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // The front office manager owns the desk and holds the guest
        // capabilities including import, so the whole directory is reachable.
        $this->grantFrontdeskRole('receptionist');

        $this->user = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);

        $this->user->assignRole('receptionist');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_guest_directory_renders(): void
    {
        Guest::create([
            'full_name' => 'Ada Directory',
            'contact_number' => '08055550001',
        ]);

        $this->actingAs($this->user)
            ->get(ListGuests::getUrl())
            ->assertSuccessful();
    }

    public function test_guest_create_form_renders(): void
    {
        $this->actingAs($this->user)
            ->get(CreateGuest::getUrl())
            ->assertSuccessful();
    }

    public function test_guest_edit_form_renders(): void
    {
        $guest = Guest::create([
            'full_name' => 'Ada Editable',
            'contact_number' => '08055550002',
        ]);

        $this->actingAs($this->user)
            ->get(EditGuest::getUrl(['record' => $guest]))
            ->assertSuccessful();
    }

    public function test_guest_view_page_renders(): void
    {
        $guest = Guest::create([
            'full_name' => 'Ada Viewable',
            'contact_number' => '08055550003',
        ]);

        $this->actingAs($this->user)
            ->get(ViewGuest::getUrl(['record' => $guest]))
            ->assertSuccessful()
            ->assertSee('Ada Viewable');
    }

    public function test_guest_can_be_created_through_the_form(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateGuest::class)
            ->fillForm([
                'title' => 'Dr.',
                'full_name' => 'Ada Created',
                'contact_number' => '08055550004',
                'email' => 'ada@example.com',
                'gender' => 'Female',
                'identification_type' => 'National ID (NIN)',
                'identification_number' => '12345678901',
                'home_address' => '1 Test Road',
                'city' => 'Abuja',
                'emergency_name' => 'Grace Created',
                'emergency_contact' => '08055550005',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $guest = Guest::where('full_name', 'Ada Created')->first();

        $this->assertNotNull($guest);
        $this->assertSame('08055550004', $guest->contact_number);
        $this->assertSame('Dr.', $guest->title);
        $this->assertSame('National ID (NIN)', $guest->identification_type);
        $this->assertSame('Grace Created', $guest->emergency_name);
    }

    public function test_phone_number_is_required_because_the_column_is_not_nullable(): void
    {
        Livewire::actingAs($this->user)
            ->test(CreateGuest::class)
            ->fillForm([
                'full_name' => 'No Phone Guest',
            ])
            ->call('create')
            ->assertHasFormErrors(['contact_number' => 'required']);
    }

    public function test_the_form_does_not_ask_for_columns_that_do_not_exist(): void
    {
        // first_name and last_name were required form fields with no matching
        // columns, so saving a guest through the form could never have worked.
        $this->assertFalse(Schema::hasColumn('guests', 'first_name'));
        $this->assertFalse(Schema::hasColumn('guests', 'last_name'));
        $this->assertTrue(Schema::hasColumn('guests', 'full_name'));
    }

    public function test_the_form_covers_the_legacy_guest_card_fields(): void
    {
        // Saved through the real form, so this proves the fields exist, are
        // accepted, and land on the guest - not merely that a component was
        // declared somewhere in the schema.
        Livewire::actingAs($this->user)
            ->test(CreateGuest::class)
            ->fillForm($this->legacyCardFields())
            ->call('create')
            ->assertHasNoFormErrors();

        $guest = Guest::where('full_name', 'Legacy Card Guest')->first();

        $this->assertNotNull($guest);

        foreach ($this->legacyCardFields() as $field => $value) {
            if ($value === null) {
                continue;
            }

            $actual = $guest->{$field};

            // Date columns come back as Carbon, so compare on the date.
            if ($actual instanceof \DateTimeInterface) {
                $actual = $actual->format('Y-m-d');
            }

            $this->assertSame(
                $value,
                $actual,
                "The guest form does not save {$field}."
            );
        }
    }

    /**
     * Everything the legacy guest card displayed, in the shape the form takes.
     *
     * @return array<string, mixed>
     */
    protected function legacyCardFields(): array
    {
        return [
            'title' => 'Mrs.',
            'full_name' => 'Legacy Card Guest',
            'gender' => 'Female',
            'birthday' => '1990-04-04',
            'nationality' => 'Nigeria',
            'identification_type' => 'International Passport',
            'identification_number' => 'A1234567',
            'contact_number' => '08055550010',
            'email' => 'legacy.card@example.com',
            'home_address' => '5 Legacy Close',
            'city' => 'Lagos',
            'state' => 'Lagos',
            'zip_code' => '100001',
            'occupation' => 'Architect',
            'company_name' => 'Legacy Ltd',
            'emergency_name' => 'Sam Emergency',
            'emergency_relationship' => 'Sibling',
            'emergency_contact' => '08055550011',
        ];
    }

    public function test_stay_totals_count_registrations_and_nights(): void
    {
        // The legacy card summed registrations.total_nights, which does not
        // exist; the column is no_of_nights. Asserted on the schema so the
        // figure cannot silently start reading the wrong column again.
        $this->assertTrue(Schema::hasColumn('registrations', 'no_of_nights'));
        $this->assertFalse(Schema::hasColumn('registrations', 'total_nights'));

        $guest = Guest::create([
            'full_name' => 'Ada Stayer',
            'contact_number' => '08055550006',
        ]);

        $this->assertSame(0, $guest->registrations()->count());
        $this->assertSame(0, (int) $guest->registrations()->sum('no_of_nights'));
    }

    public function test_stay_history_relation_manager_is_registered(): void
    {
        // The individual visit record the directory did not previously expose.
        $this->assertContains(
            RegistrationsRelationManager::class,
            GuestResource::getRelations()
        );
    }

    public function test_guest_with_stays_cannot_be_deleted(): void
    {
        // The legacy controller refused this. Deleting the profile would orphan
        // the registrations, so the guard has to hold in Filament too.
        $guest = Guest::create([
            'full_name' => 'Ada Has Stays',
            'contact_number' => '08055550007',
        ]);

        // Without an actual registration the guard has nothing to find, so the
        // guest has to really have stayed before this proves anything.
        Registration::create([
            'guest_id' => $guest->id,
            'reservation_code' => 'ADA-0001',
            'title' => 'Ms',
            'full_name' => 'Ada Has Stays',
            'contact_number' => '08055550007',
            'check_in' => now()->subDays(3)->toDateString(),
            'check_out' => now()->subDay()->toDateString(),
            'no_of_nights' => 2,
        ]);

        Livewire::actingAs($this->user)
            ->test(ListGuests::class)
            ->callTableAction('delete', $guest->getKey())
            // The guard halts before the delete, so nothing is dispatched.
            ->assertNotDispatched('deleted');

        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    }

    public function test_guest_with_no_stays_can_be_deleted(): void
    {
        $guest = Guest::create([
            'full_name' => 'Ada Disposable',
            'contact_number' => '08055550008',
        ]);

        Livewire::actingAs($this->user)
            ->test(ListGuests::class)
            ->callTableAction('delete', $guest->getKey());

        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
    }

    public function test_the_stats_widget_renders_its_own_figures(): void
    {
        Guest::create([
            'full_name' => 'Ada Counted',
            'contact_number' => '08055550009',
        ]);

        // The widget is asserted directly rather than through the directory's
        // HTML: Filament renders header widgets as separate Livewire children,
        // so their contents are not in the parent page's markup.
        Livewire::actingAs($this->user)
            ->test(GuestStatsOverview::class)
            ->assertSuccessful()
            ->assertSee('Total guests')
            ->assertSee('Returning guests');
    }

    public function test_the_directory_registers_the_stats_widget(): void
    {
        $method = new \ReflectionMethod(ListGuests::class, 'getHeaderWidgets');
        $method->setAccessible(true);

        $this->assertContains(
            GuestStatsOverview::class,
            $method->invoke(app(ListGuests::class))
        );
    }

    public function test_stats_widget_lists_the_legacy_figures(): void
    {
        $method = new \ReflectionMethod(GuestStatsOverview::class, 'getStats');
        $method->setAccessible(true);

        $labels = collect($method->invoke(app(GuestStatsOverview::class)))
            ->map(fn ($stat): string => $stat->getLabel())
            ->all();

        // The four tiles the Blade index showed above its table.
        $this->assertContains('Total guests', $labels);
        $this->assertContains('Recent arrivals', $labels);
        $this->assertContains('Returning guests', $labels);
        $this->assertContains('New this month', $labels);
    }

    public function test_import_action_on_the_directory_points_at_the_filament_page(): void
    {
        $this->actingAs($this->user)
            ->get(ListGuests::getUrl())
            ->assertSuccessful()
            ->assertSee(ImportGuests::getUrl(), escape: false);

        $this->actingAs($this->user)
            ->get(ImportGuests::getUrl())
            ->assertSuccessful()
            ->assertSee('Import guests');
    }
}
