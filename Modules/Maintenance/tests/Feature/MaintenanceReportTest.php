<?php

namespace Modules\Maintenance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Maintenance\Database\Seeders\MaintenanceReportSeeder;
use Modules\Maintenance\Models\MaintenanceLog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MaintenanceReportTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $permission = Permission::firstOrCreate([
            'name' => 'maintenance.read',
            'guard_name' => 'web',
        ]);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->user->givePermissionTo($permission);
    }

    public function test_report_page_loads_for_authorized_user()
    {
        $response = $this->actingAs($this->user)->get(route('maintenance.report'));

        $response->assertOk();
        $response->assertViewIs('maintenance::report');
        $response->assertSee('Maintenance Report');
    }

    public function test_report_summary_totals_are_calculated_correctly()
    {
        (new MaintenanceReportSeeder)->run();

        $response = $this->actingAs($this->user)->get(route('maintenance.report'));

        $response->assertOk();
        $summary = $response->viewData('summary');

        $this->assertEquals(10, $summary['total']);
        $this->assertEquals(4, $summary['open']);       // new + in_progress
        $this->assertEquals(5, $summary['completed']);  // status = completed
        // Sum of cost_of_fixing on completed jobs: 45000 + 18500.50 + 120000 + 5000 + 32000.
        $this->assertEquals(220500.50, (float) $summary['totalCost']);
    }

    public function test_report_can_be_filtered_by_department()
    {
        (new MaintenanceReportSeeder)->run();

        $response = $this->actingAs($this->user)
            ->get(route('maintenance.report', ['department' => 'Plumbing']));

        $response->assertOk();
        $summary = $response->viewData('summary');
        $logs = $response->viewData('logs');

        $this->assertEquals(2, $summary['total']);
        $this->assertEquals(2, $summary['completed']);
        $this->assertEquals(0, $summary['open']);
        $this->assertTrue($logs->every(fn ($log) => $log->department === 'Plumbing'));
    }

    public function test_report_can_be_filtered_by_status()
    {
        (new MaintenanceReportSeeder)->run();

        $response = $this->actingAs($this->user)
            ->get(route('maintenance.report', ['status' => 'completed']));

        $response->assertOk();
        $logs = $response->viewData('logs');

        $this->assertEquals(5, $response->viewData('summary')['total']);
        $this->assertTrue($logs->every(fn ($log) => $log->status === 'completed'));
    }

    public function test_report_can_be_filtered_by_date_range()
    {
        (new MaintenanceReportSeeder)->run();

        // The seeder's oldest job is 90 days old; a 40-day window excludes it.
        $from = now()->subDays(40)->toDateString();

        $response = $this->actingAs($this->user)
            ->get(route('maintenance.report', ['from' => $from]));

        $response->assertOk();
        $this->assertEquals(9, $response->viewData('summary')['total']);
    }

    public function test_report_can_be_exported_as_pdf()
    {
        (new MaintenanceReportSeeder)->run();

        $response = $this->actingAs($this->user)
            ->post(route('maintenance.report.export'), ['status' => 'completed']);

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('maintenance-report-', $response->headers->get('content-disposition'));
    }

    public function test_unauthenticated_user_is_redirected_from_report()
    {
        $response = $this->get(route('maintenance.report'));

        $response->assertRedirect();
    }

    public function test_user_without_permission_cannot_access_report()
    {
        $unauthorized = User::factory()->create(['type' => 'staff', 'status' => 'active']);

        $response = $this->actingAs($unauthorized)->get(route('maintenance.report'));

        $response->assertForbidden();
    }

    public function test_seeder_populates_representative_maintenance_logs()
    {
        (new MaintenanceReportSeeder)->run();

        $this->assertEquals(10, MaintenanceLog::count());
        $this->assertGreaterThanOrEqual(1, MaintenanceLog::byDepartment('Plumbing')->count());
        $this->assertGreaterThanOrEqual(1, MaintenanceLog::completed()->whereNotNull('cost_of_fixing')->count());
    }
}
