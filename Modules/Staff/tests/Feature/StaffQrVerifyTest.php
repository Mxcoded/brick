<?php

namespace Modules\Staff\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Staff\Models\Employee;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffQrVerifyTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);
    }

    private function makeEmployee(string $staffCode, ?string $startDate): Employee
    {
        return Employee::create([
            'name' => 'Verify Staff '.$staffCode,
            'email' => 'verify.'.$staffCode.'.'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Waiter',
            'department' => 'Restaurant',
            'branch_name' => 'Asokoro',
            'status' => 'approved',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1995-01-01',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'residential_address' => '1 Test Street',
            'next_of_kin_name' => 'Test Kin',
            'next_of_kin_phone' => '08012345678',
            'ice_contact_name' => 'ICE',
            'ice_contact_phone' => '08087654321',
            'staff_code' => $staffCode,
            'start_date' => $startDate,
        ]);
    }

    #[Test]
    public function staffqr_shows_each_staffs_own_start_date(): void
    {
        $employeeA = $this->makeEmployee('9101', '2023-04-15');
        $employeeB = $this->makeEmployee('9102', '2024-11-02');

        $response = $this->post(route('staff.verify.lookup'), ['staff_code' => $employeeA->staff_code]);
        $response->assertSessionHas('verified');
        $this->assertSame('April 2023', session('verified')['employed_since']);

        $response = $this->post(route('staff.verify.lookup'), ['staff_code' => $employeeB->staff_code]);
        $response->assertSessionHas('verified');
        $this->assertSame('November 2024', session('verified')['employed_since']);
    }

    #[Test]
    public function staffqr_does_not_return_a_fatal_error_on_string_start_date(): void
    {
        $employee = $this->makeEmployee('9103', '2025-06-17');

        $response = $this->post(route('staff.verify.lookup'), ['staff_code' => $employee->staff_code]);

        $response->assertRedirect();
        $response->assertSessionHas('verified');
        $this->assertSame('June 2025', session('verified')['employed_since']);
    }

    #[Test]
    public function staffqr_falls_back_to_created_at_when_start_date_missing(): void
    {
        $employee = $this->makeEmployee('9104', null);

        $response = $this->post(route('staff.verify.lookup'), ['staff_code' => $employee->staff_code]);

        $response->assertRedirect();
        $response->assertSessionHas('verified');
        $this->assertSame($employee->created_at->format('F Y'), session('verified')['employed_since']);
    }

    #[Test]
    public function staffqr_returns_error_for_unknown_code(): void
    {
        $response = $this->post(route('staff.verify.lookup'), ['staff_code' => '0000']);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('verified');
    }
}
