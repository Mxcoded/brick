<?php

namespace Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Support\LandingPage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Database\Seeders\FinancePermissionSeeder;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Locks in where each kind of user ends up after logging in, and what happens
 * when they open the admin panel.
 */
class LoginRedirectTest extends TestCase
{
    use DatabaseTransactions;
    use GrantsRestaurantRoles;

    protected function setUp(): void
    {
        parent::setUp();

        (new FinancePermissionSeeder)->run();
    }

    protected function financeUser(): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(Role::findOrCreate('finance', 'web'));

        return $user;
    }

    protected function adminUser(): User
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(Role::findOrCreate('admin', 'web'));

        return $user;
    }

    protected function login(User $user)
    {
        return $this->post('/login', ['email' => $user->email, 'password' => 'password']);
    }

    /**
     * A staff user holding exactly what config/restaurant.php grants the role.
     */
    protected function restaurantUser(string $roleName): User
    {
        $role = $this->grantRestaurantRole($roleName);

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole($role);

        return $user;
    }

    protected function waiterUser(): User
    {
        return $this->restaurantUser(RoleEnum::WAITER->value);
    }

    public function test_admin_login_lands_on_the_executive_dashboard(): void
    {
        $this->login($this->adminUser())
            ->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_finance_login_lands_on_the_finance_dashboard(): void
    {
        $this->login($this->financeUser())
            ->assertRedirect(route('filament.admin.pages.finance'));
    }

    public function test_finance_user_with_the_staff_role_still_lands_on_finance(): void
    {
        $user = $this->financeUser();
        $user->assignRole(Role::findOrCreate('staff', 'web'));

        $this->login($user)
            ->assertRedirect(route('filament.admin.pages.finance'));
    }

    public function test_panel_root_is_reachable_by_a_finance_user(): void
    {
        $this->actingAs($this->financeUser());

        $this->get('/admin')->assertOk();
    }

    public function test_panel_root_is_reachable_by_a_plain_admin(): void
    {
        $this->actingAs($this->adminUser());

        $this->get('/admin')->assertOk();
    }

    public function test_finance_user_can_open_the_finance_dashboard(): void
    {
        $this->actingAs($this->financeUser());

        $this->get('/admin/finance')->assertOk();
    }

    public function test_finance_user_cannot_open_the_executive_dashboard(): void
    {
        $this->actingAs($this->financeUser());

        $this->get('/admin/executive-dashboard')->assertForbidden();
    }

    public function test_guest_is_sent_to_the_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_user_without_panel_access_is_redirected_not_forbidden(): void
    {
        $user = User::factory()->create(['type' => 'guest', 'status' => 'active']);

        $this->actingAs($user);

        $this->get('/admin')
            ->assertRedirect(route('guest.dashboard'))
            ->assertSessionHas('warning');
    }

    public function test_landing_page_prefers_admin_over_finance(): void
    {
        $user = $this->financeUser();
        $user->givePermissionTo('access_admin_dashboard');

        $this->assertSame(
            'filament.admin.pages.dashboard',
            LandingPage::routeFor($user),
        );
    }

    public function test_landing_page_falls_back_to_guest_dashboard(): void
    {
        $user = User::factory()->create(['type' => 'guest', 'status' => 'active']);

        $this->assertSame('guest.dashboard', LandingPage::routeFor($user));
    }

    public function test_waiter_login_lands_on_the_pos_terminal(): void
    {
        $this->login($this->waiterUser())
            ->assertRedirect(route('restaurant.waiter.dashboard'));
    }

    public function test_waiter_following_the_login_redirect_reaches_a_working_pos(): void
    {
        $user = $this->waiterUser();

        $this->login($user)
            ->assertRedirect(route('restaurant.waiter.dashboard'));

        $this->actingAs($user)
            ->get(route('restaurant.waiter.dashboard'))
            ->assertOk();
    }

    public function test_landing_page_sends_floor_staff_to_pos_not_the_staff_dashboard(): void
    {
        $this->assertSame(
            'restaurant.waiter.dashboard',
            LandingPage::routeFor($this->waiterUser()),
        );
    }

    public function test_restaurant_manager_login_lands_on_the_back_office(): void
    {
        $this->login($this->restaurantUser(RoleEnum::RESTAURANT_MANAGER->value))
            ->assertRedirect(route('restaurant.admin.dashboard'));
    }

    public function test_home_redirects_through_the_same_landing_page_as_login(): void
    {
        // /home used to run its own if-chain that knew nothing about Finance or
        // Restaurant, so a waiter who landed here was sent to the task list. It
        // now delegates to LandingPage, giving the same answer as login.
        $user = $this->waiterUser();

        $this->actingAs($user)
            ->get('/home')
            ->assertRedirect(route(LandingPage::routeFor($user)));
    }
}
