<?php

namespace Modules\Staff\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Modules\Staff\Models\Employee;
use Modules\Staff\Models\EmployeeSkill;
use Modules\Staff\Models\PerformanceReview;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    private Employee $employee;

    private Employee $reviewer;

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

        $code = 'PERF'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->employee = Employee::create([
            'name' => 'Performance Test Staff',
            'email' => 'perf_test_'.uniqid().'@brickspoint.com',
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

        $this->reviewer = Employee::create([
            'name' => 'Reviewer Staff',
            'email' => 'reviewer_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Manager',
            'department' => 'Front Office',
            'branch_name' => 'Asokoro',
            'status' => 'approved',
            'place_of_birth' => 'Abuja',
            'state_of_origin' => 'FCT',
            'lga' => 'Abuja Municipal',
            'nationality' => 'Nigerian',
            'gender' => 'Male',
            'date_of_birth' => '1985-06-20',
            'marital_status' => 'Married',
            'blood_group' => 'A+',
            'genotype' => 'AS',
            'residential_address' => '456 Review Street, Abuja',
            'next_of_kin_name' => 'Jane Doe',
            'next_of_kin_phone' => '080'.random_int(10000000, 99999999),
            'ice_contact_name' => 'John Doe',
            'ice_contact_phone' => '080'.random_int(10000000, 99999999),
            'start_date' => now()->subYears(3),
            'staff_code' => 'PERF'.str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'user_id' => $this->user->id,
        ]);
    }

    public function test_performance_index_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.performance.index'))
            ->assertStatus(200)
            ->assertSee('Performance Reviews')
            ->assertSee('Total Reviews');
    }

    public function test_performance_create_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.performance.create'))
            ->assertStatus(200)
            ->assertSee('New Performance Review')
            ->assertSee('Punctuality & Attendance');
    }

    public function test_performance_store_creates_review()
    {
        $this->actingAs($this->user)
            ->post(route('staff.performance.store'), [
                'employee_id' => $this->employee->id,
                'review_date' => now()->format('Y-m-d'),
                'review_period' => 'quarterly',
                'rating_punctuality' => 4,
                'rating_teamwork' => 5,
                'rating_communication' => 4,
                'rating_quality' => 5,
                'rating_initiative' => 4,
                'strengths' => 'Excellent communication',
                'areas_for_improvement' => 'Needs more initiative',
                'comments' => 'Good overall performer',
            ])
            ->assertRedirect(route('staff.performance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('performance_reviews', [
            'employee_id' => $this->employee->id,
            'review_period' => 'quarterly',
        ]);
    }

    public function test_performance_calculates_overall_score()
    {
        $this->actingAs($this->user)
            ->post(route('staff.performance.store'), [
                'employee_id' => $this->employee->id,
                'review_date' => now()->format('Y-m-d'),
                'review_period' => 'annual',
                'rating_punctuality' => 3,
                'rating_teamwork' => 3,
                'rating_communication' => 3,
                'rating_quality' => 3,
                'rating_initiative' => 3,
            ]);

        $review = PerformanceReview::where('employee_id', $this->employee->id)->first();
        $this->assertEquals(3.0, $review->overall_score);
    }

    public function test_performance_show_page_loads()
    {
        $review = PerformanceReview::create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'review_date' => now()->format('Y-m-d'),
            'review_period' => 'quarterly',
            'rating_punctuality' => 4,
            'rating_teamwork' => 5,
            'rating_communication' => 4,
            'rating_quality' => 5,
            'rating_initiative' => 4,
            'overall_score' => 4.4,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.performance.show', $review))
            ->assertStatus(200)
            ->assertSee('Performance Test Staff')
            ->assertSee('4.4');
    }

    public function test_performance_edit_page_loads()
    {
        $review = PerformanceReview::create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'review_date' => now()->format('Y-m-d'),
            'review_period' => 'quarterly',
            'rating_punctuality' => 4,
            'rating_teamwork' => 5,
            'rating_communication' => 4,
            'rating_quality' => 5,
            'rating_initiative' => 4,
            'overall_score' => 4.4,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->user)
            ->get(route('staff.performance.edit', $review))
            ->assertStatus(200)
            ->assertSee('Edit Performance Review');
    }

    public function test_performance_update_review()
    {
        $review = PerformanceReview::create([
            'employee_id' => $this->employee->id,
            'reviewer_id' => $this->reviewer->id,
            'review_date' => now()->format('Y-m-d'),
            'review_period' => 'quarterly',
            'rating_punctuality' => 3,
            'rating_teamwork' => 3,
            'rating_communication' => 3,
            'rating_quality' => 3,
            'rating_initiative' => 3,
            'overall_score' => 3.0,
            'status' => 'submitted',
        ]);

        $this->actingAs($this->user)
            ->put(route('staff.performance.update', $review), [
                'employee_id' => $this->employee->id,
                'review_date' => now()->format('Y-m-d'),
                'review_period' => 'annual',
                'rating_punctuality' => 5,
                'rating_teamwork' => 5,
                'rating_communication' => 5,
                'rating_quality' => 5,
                'rating_initiative' => 5,
                'strengths' => 'Updated strengths',
            ])
            ->assertRedirect(route('staff.performance.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('performance_reviews', [
            'id' => $review->id,
            'review_period' => 'annual',
            'overall_score' => 5.0,
        ]);
    }

    public function test_performance_store_validates_required_fields()
    {
        $this->actingAs($this->user)
            ->post(route('staff.performance.store'), [])
            ->assertSessionHasErrors([
                'employee_id', 'review_date', 'review_period',
                'rating_punctuality', 'rating_teamwork',
                'rating_communication', 'rating_quality', 'rating_initiative',
            ]);
    }

    public function test_skills_index_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.performance.skills'))
            ->assertStatus(200)
            ->assertSee('Skills Matrix')
            ->assertSee('Add Skill');
    }

    public function test_skills_create_page_loads()
    {
        $this->actingAs($this->user)
            ->get(route('staff.performance.skills-create'))
            ->assertStatus(200)
            ->assertSee('Add Skill')
            ->assertSee('Skill Name');
    }

    public function test_skills_store_creates_skill()
    {
        $this->actingAs($this->user)
            ->post(route('staff.performance.skills-store'), [
                'employee_id' => $this->employee->id,
                'skill_name' => 'Python Programming',
                'category' => 'technical',
                'proficiency_level' => 'advanced',
                'years_experience' => 3,
                'is_certified' => true,
            ])
            ->assertRedirect(route('staff.performance.skills'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $this->employee->id,
            'skill_name' => 'Python Programming',
            'category' => 'technical',
        ]);
    }

    public function test_skills_store_validates_unique_skill()
    {
        EmployeeSkill::create([
            'employee_id' => $this->employee->id,
            'skill_name' => 'Python Programming',
            'category' => 'technical',
            'proficiency_level' => 'advanced',
        ]);

        $this->actingAs($this->user)
            ->post(route('staff.performance.skills-store'), [
                'employee_id' => $this->employee->id,
                'skill_name' => 'Python Programming',
                'category' => 'technical',
                'proficiency_level' => 'advanced',
            ])
            ->assertSessionHasErrors(['skill_name']);
    }

    public function test_skills_destroy_deletes_skill()
    {
        $skill = EmployeeSkill::create([
            'employee_id' => $this->employee->id,
            'skill_name' => 'PHP',
            'category' => 'technical',
            'proficiency_level' => 'expert',
        ]);

        $this->actingAs($this->user)
            ->delete(route('staff.performance.skills-destroy', $skill))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('employee_skills', ['id' => $skill->id]);
    }

    public function test_performance_requires_authentication()
    {
        $this->get(route('staff.performance.index'))
            ->assertRedirect(route('login'));

        $this->get(route('staff.performance.skills'))
            ->assertRedirect(route('login'));
    }
}
