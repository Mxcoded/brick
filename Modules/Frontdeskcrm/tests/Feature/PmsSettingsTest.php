<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Frontdeskcrm\Filament\Pages\PmsSettings;
use Modules\Frontdeskcrm\Models\FrontdeskSetting;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Modules\Frontdeskcrm\Tests\Concerns\GrantsFrontdeskRoles;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The PMS settings page.
 *
 * Automatic room charging arms a scheduler that posts money, so the page has to
 * actually render, prefill with the saved values, save them, and refuse to save
 * for a reader - the same blunt checks that caught two fatal faults in the
 * Restaurant settings page when nothing ever rendered it.
 */
class PmsSettingsTest extends TestCase
{
    use DatabaseTransactions, GrantsFrontdeskRoles;

    protected function setUp(): void
    {
        parent::setUp();

        // GM holds the whole taxonomy via the config map, so this role can both
        // see and edit the settings screen.
        $this->grantFrontdeskRole('gm');
    }

    private function gm(): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole('gm');

        return $user->fresh();
    }

    #[Test]
    public function the_page_renders_and_is_gated_by_the_read_capability(): void
    {
        $this->actingAs($this->gm());

        $this->assertTrue(PmsSettings::canAccess());

        Livewire::test(PmsSettings::class)
            ->assertOk()
            ->assertSee('Automatic room charging')
            ->assertSee('Charge time');
    }

    #[Test]
    public function a_user_without_the_capability_cannot_see_it(): void
    {
        $this->grantFrontdeskPermissions('guest_reader', []);

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole('guest_reader');

        $this->actingAs($user);

        $this->assertFalse(PmsSettings::canAccess());
    }

    #[Test]
    public function it_opens_prefilled_with_the_saved_values(): void
    {
        FrontdeskSetting::setValue('auto_charge_enabled', '1');
        FrontdeskSetting::setValue('auto_charge_time', '05:30');

        $this->actingAs($this->gm());

        $page = Livewire::test(PmsSettings::class)->assertOk()->instance();
        $state = $page->form->getState();

        $this->assertTrue($state['auto_charge_enabled']);
        $this->assertSame('05:30', (string) $state['auto_charge_time']);
    }

    #[Test]
    public function it_falls_back_to_defaults_on_a_fresh_install(): void
    {
        $this->actingAs($this->gm());

        $page = Livewire::test(PmsSettings::class)->assertOk()->instance();
        $state = $page->form->getState();

        // Never a blank form: charging stays off and the time is a sane default
        // that the scheduler also falls back to.
        $this->assertFalse($state['auto_charge_enabled']);
        $this->assertNotSame('', (string) $state['auto_charge_time']);
        $this->assertSame('02:00', (string) $state['auto_charge_time']);
    }

    #[Test]
    public function a_manager_saves_the_settings(): void
    {
        $this->actingAs($this->gm());

        Livewire::test(PmsSettings::class)
            ->assertOk()
            ->fillForm([
                'auto_charge_enabled' => true,
                'auto_charge_time' => '03:15',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('1', (string) FrontdeskSetting::getValue('auto_charge_enabled'));
        $this->assertSame('03:15', (string) FrontdeskSetting::getValue('auto_charge_time'));
    }

    #[Test]
    public function a_reader_cannot_save(): void
    {
        $readOnly = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $readOnly->givePermissionTo(
            Permission::findOrCreate(FrontdeskPermission::PMS_SETTINGS_READ, 'web')
        );

        FrontdeskSetting::setValue('auto_charge_enabled', '0');
        FrontdeskSetting::setValue('auto_charge_time', '02:00');

        $this->actingAs($readOnly->fresh());

        $this->assertTrue(PmsSettings::canAccess());
        $this->assertFalse((new PmsSettings)->canEdit());

        Livewire::test(PmsSettings::class)
            ->assertOk()
            ->fillForm([
                'auto_charge_enabled' => true,
                'auto_charge_time' => '03:15',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        // Unchanged: the refusal is the whole point of the capability split.
        $this->assertSame('0', (string) FrontdeskSetting::getValue('auto_charge_enabled'));
        $this->assertSame('02:00', (string) FrontdeskSetting::getValue('auto_charge_time'));
    }

    #[Test]
    public function reset_returns_the_form_to_the_saved_values(): void
    {
        FrontdeskSetting::setValue('auto_charge_time', '05:00');

        $this->actingAs($this->gm());

        $page = Livewire::test(PmsSettings::class)
            ->assertOk()
            ->fillForm(['auto_charge_time' => '23:00'])
            ->call('resetForm')
            ->assertHasNoFormErrors()
            ->instance();

        $this->assertSame('05:00', (string) $page->form->getState()['auto_charge_time']);
    }
}
