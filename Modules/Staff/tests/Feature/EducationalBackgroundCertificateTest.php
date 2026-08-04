<?php

namespace Modules\Staff\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Staff\Models\EducationalBackground;
use Modules\Staff\Models\Employee;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EducationalBackgroundCertificateTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'employees.read', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);

        $this->employee = Employee::create([
            'name' => 'John Doe',
            'email' => 'john'.uniqid().'@example.com',
            'place_of_birth' => 'Lagos',
            'state_of_origin' => 'Lagos',
            'lga' => 'Ikeja',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'phone_number' => '080'.rand(10000000, 99999999),
            'position' => 'Manager',
            'department' => 'Ops',
            'residential_address' => '123 Staff Road',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '080'.rand(10000000, 99999999),
            'ice_contact_name' => 'Jane Doe',
            'ice_contact_phone' => '080'.rand(10000000, 99999999),
        ]);
    }

    private function educationWithCertificate(?string $path): EducationalBackground
    {
        return EducationalBackground::create([
            'employee_id' => $this->employee->id,
            'school_name' => 'University of Lagos',
            'start_date' => '2010-01-01',
            'end_date' => '2014-01-01',
            'qualification' => 'B.Sc.',
            'certificate_path' => $path,
        ]);
    }

    /**
     * The download route must stream the file from the public disk through PHP
     * (bypassing the public/storage junction that returns 403 on cPanel).
     */
    public function test_download_certificate_streams_file_from_public_disk()
    {
        Storage::fake('public');
        Storage::disk('public')->put('certificates/cert.pdf', '%PDF-1.4 fake certificate');

        $education = $this->educationWithCertificate('certificates/cert.pdf');

        $response = $this->get(route('staff.education.certificate', $education));

        $response->assertOk();
        $this->assertSame('%PDF-1.4 fake certificate', $response->streamedContent());
    }

    public function test_download_certificate_returns_404_when_file_missing()
    {
        Storage::fake('public');

        $education = $this->educationWithCertificate('certificates/gone.pdf');

        $this->get(route('staff.education.certificate', $education))->assertNotFound();
    }

    public function test_download_certificate_requires_employees_read_permission()
    {
        Storage::fake('public');
        Storage::disk('public')->put('certificates/cert.pdf', 'content');

        $education = $this->educationWithCertificate('certificates/cert.pdf');

        $plain = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->actingAs($plain);

        $this->get(route('staff.education.certificate', $education))->assertForbidden();
    }

    public function test_download_certificate_requires_authentication()
    {
        Storage::fake('public');
        Storage::disk('public')->put('certificates/cert.pdf', 'content');

        $education = $this->educationWithCertificate('certificates/cert.pdf');

        Auth::logout();

        $this->get(route('staff.education.certificate', $education))->assertRedirect(route('login'));
    }
}
