<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Restaurant\Filament\Pages\RestaurantSettings;
use Modules\Restaurant\Models\RestaurantSetting;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The Restaurant settings page.
 *
 * This page carried two faults that no test could see, because nothing ever
 * rendered it: `form()` was typed against `Filament\Forms\Form` when Filament
 * passes a `Filament\Schemas\Schema`, and the `statePath('settings')` pointed at
 * a property that did not exist. Both were fatal on every page load, in the
 * browser as well as here. The assertions below are deliberately blunt --
 * render, prefill, save, refuse -- because the lesson is that a Filament page
 * which is never rendered is a page which is never known to work.
 */
class RestaurantSettingsTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);
    }

    #[Test]
    public function the_page_renders_and_is_gated_by_the_read_capability(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $this->assertTrue(RestaurantSettings::canAccess());

        Livewire::test(RestaurantSettings::class)
            ->assertOk()
            ->assertSee('VAT rate')
            ->assertSee('Service charge');
    }

    #[Test]
    public function a_user_without_the_capability_cannot_see_it(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $this->assertFalse(RestaurantSettings::canAccess());
    }

    #[Test]
    public function it_opens_prefilled_with_the_saved_values(): void
    {
        RestaurantSetting::setValue('vat_rate', '5');
        RestaurantSetting::setValue('service_charge_rate', '7.5');

        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $page = Livewire::test(RestaurantSettings::class)->assertOk()->instance();

        $state = $page->form->getState();

        $this->assertSame('5', (string) $state['vat_rate']);
        $this->assertSame('7.5', (string) $state['service_charge_rate']);
    }

    #[Test]
    public function it_falls_back_to_defaults_on_a_fresh_install(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $page = Livewire::test(RestaurantSettings::class)->assertOk()->instance();
        $state = $page->form->getState();

        // Never a blank form, which would invite saving zeroes over a venue
        // that has not configured anything yet.
        $this->assertNotSame('', (string) $state['vat_rate']);
        $this->assertNotSame('', (string) $state['shift_start_time']);
    }

    #[Test]
    public function a_manager_saves_the_rates(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        Livewire::test(RestaurantSettings::class)
            ->assertOk()
            ->fillForm([
                'vat_rate' => '7.5',
                'service_charge_rate' => '10',
                'discount_limit' => '20',
                'shift_start_time' => '07:00',
                'shift_end_time' => '23:00',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('7.5', (string) RestaurantSetting::getValue('vat_rate'));
        $this->assertSame('20', (string) RestaurantSetting::getValue('discount_limit'));
        $this->assertSame('07:00', (string) RestaurantSetting::getValue('shift_start_time'));
    }

    #[Test]
    public function a_reader_cannot_save(): void
    {
        $reader = $this->staffWith(RoleEnum::RESTAURANT_MANAGER->value);
        $reader->revokePermissionTo(
            Permission::findOrCreate(RestaurantPermission::SETTINGS_UPDATE, 'web')
        );

        // A manager whose update grant is withdrawn still holds it through the
        // role, so strip the role too and grant the read alone.
        $readOnly = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $readOnly->givePermissionTo(
            Permission::findOrCreate(RestaurantPermission::SETTINGS_READ, 'web')
        );

        RestaurantSetting::setValue('vat_rate', '5');

        $this->actingAs($readOnly->fresh());

        $this->assertTrue(RestaurantSettings::canAccess());

        Livewire::test(RestaurantSettings::class)
            ->assertOk()
            ->fillForm(['vat_rate' => '50'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Unchanged: the refusal is the whole point of the capability split.
        $this->assertSame('5', (string) RestaurantSetting::getValue('vat_rate'));
    }

    #[Test]
    public function reset_returns_the_form_to_the_saved_values(): void
    {
        RestaurantSetting::setValue('vat_rate', '5');

        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $page = Livewire::test(RestaurantSettings::class)
            ->assertOk()
            ->fillForm(['vat_rate' => '99'])
            ->call('resetForm')
            ->assertHasNoFormErrors()
            ->instance();

        $this->assertSame('5', (string) $page->form->getState()['vat_rate']);
    }

    #[Test]
    public function the_rates_are_rejected_when_outside_sane_bounds(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        Livewire::test(RestaurantSettings::class)
            ->assertOk()
            ->fillForm(['vat_rate' => '500'])
            ->call('save')
            ->assertHasFormErrors(['vat_rate']);
    }

    private function staffWith(string $roleName): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $user->assignRole(Role::findOrCreate($roleName, 'web'));

        return $user->fresh();
    }
}
