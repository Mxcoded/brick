<?php

namespace Modules\Staff\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\TrainingRecord;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TrainingTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'access_staff_dashboard', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'employees.read', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
            'password' => Hash::make('password'),
        ]);
        $this->user->givePermissionTo(['access_staff_dashboard', 'employees.read']);

        $code = 'TRN'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->employee = Employee::create([
            'name' => 'Training Test Staff',
            'email' => 'trn_test_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Front Desk Officer',
            'department' => 'Front Office',
            'branch_name' => 'Asokoro',
            'status' => 'approved',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-15',
            'marital_status' => 'Single',
            'blood_group' => 'O+',
            'genotype' => 'AA',
            'residential_address' => '123 Test Street, Abuja',
            'next_of_kin_name' => 'John Doe',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'Jane Doe',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
            'start_date' => now()->subYear(),
            'staff_code' => $code,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_training_index_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.training.index'))
            ->assertStatus(200)
            ->assertSee('Training & Certifications')
            ->assertSee('Total Records');
    }

    public function test_training_create_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.training.create'))
            ->assertStatus(200)
            ->assertSee('New Training Record')
            ->assertSee('Course Name');
    }

    public function test_training_store_creates_record()
    {
        $this->actingAs($this->user)
            ->post(route('staff.training.store'), [
                'employee_id' => $this->employee->id,
                'course_name' => 'Fire Safety Training',
                'provider' => 'Fire Dept',
                'training_type' => 'internal',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addDays(2)->format('Y-m-d'),
                'duration_hours' => 8,
                'status' => 'completed',
                'certification_name' => 'Fire Safety Certificate',
                'notes' => 'Completed successfully',
            ])
            ->assertRedirect(route('staff.training.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('training_records', [
            'employee_id' => $this->employee->id,
            'course_name' => 'Fire Safety Training',
            'status' => 'completed',
        ]);
    }

    public function test_training_edit_page_loads()
    {
        $record = TrainingRecord::create([
            'employee_id' => $this->employee->id,
            'course_name' => 'Customer Service',
            'training_type' => 'internal',
            'start_date' => now()->format('Y-m-d'),
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.training.edit', $record))
            ->assertStatus(200)
            ->assertSee('Edit Training Record')
            ->assertSee('Customer Service');
    }

    public function test_training_update_record()
    {
        $record = TrainingRecord::create([
            'employee_id' => $this->employee->id,
            'course_name' => 'Old Course',
            'training_type' => 'internal',
            'start_date' => now()->format('Y-m-d'),
            'status' => 'in_progress',
        ]);

        $this->actingAs($this->user)
            ->put(route('staff.training.update', $record), [
                'employee_id' => $this->employee->id,
                'course_name' => 'Updated Course',
                'training_type' => 'external',
                'start_date' => now()->format('Y-m-d'),
                'status' => 'completed',
                'certification_name' => 'Updated Cert',
            ])
            ->assertRedirect(route('staff.training.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('training_records', [
            'id' => $record->id,
            'course_name' => 'Updated Course',
            'status' => 'completed',
        ]);
    }

    public function test_training_destroy_deletes_record()
    {
        $record = TrainingRecord::create([
            'employee_id' => $this->employee->id,
            'course_name' => 'To Delete',
            'training_type' => 'internal',
            'start_date' => now()->format('Y-m-d'),
            'status' => 'enrolled',
        ]);

        $this->actingAs($this->user)
            ->delete(route('staff.training.destroy', $record))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('training_records', ['id' => $record->id]);
    }

    public function test_training_store_validates_required_fields()
    {
        $this->actingAs($this->user)
            ->post(route('staff.training.store'), [])
            ->assertSessionHasErrors([
                'employee_id', 'course_name', 'training_type',
                'start_date', 'status',
            ]);
    }

    public function test_training_requires_authentication()
    {
        $this->get(route('staff.training.index'))
            ->assertRedirect(route('login'));

        $this->get(route('staff.training.create'))
            ->assertRedirect(route('login'));
    }
}
