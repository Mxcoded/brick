<?php

namespace Modules\Tasks\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Services\TaskRecurrenceService;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RecurringTaskTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->user = User::factory()->create();
        $this->employee = Employee::create([
            'name' => 'Test Staff',
            'email' => 'task_test_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Staff',
            'department' => 'Operations',
            'branch_name' => 'Asokoro',
            'status' => 'approved',
            'user_id' => $this->user->id,
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
        ]);
        Permission::firstOrCreate(['name' => 'access_tasks_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_tasks_dashboard');
    }

    #[Test]
    public function test_creates_recurring_task_with_valid_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('tasks.store'), [
            'description' => 'Daily room inspection',
            'priority' => 'high',
            'deadline' => Carbon::today()->toDateString(),
            'is_recurring' => '1',
            'recurrence_type' => 'daily',
            'recurrence_end_date' => Carbon::today()->addMonth()->toDateString(),
        ]);

        $response->assertRedirect(route('tasks.index'));

        $task = Task::where('description', 'Daily room inspection')->first();
        $this->assertTrue($task->is_recurring);
        $this->assertEquals('daily', $task->recurrence_type);
        $this->assertNotNull($task->recurrence_end_date);
    }

    #[Test]
    public function test_non_recurring_task_has_no_recurrence_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('tasks.store'), [
            'description' => 'One-time task',
            'priority' => 'medium',
            'deadline' => Carbon::today()->toDateString(),
        ]);

        $response->assertRedirect(route('tasks.index'));

        $task = Task::where('description', 'One-time task')->first();
        $this->assertFalse($task->is_recurring);
        $this->assertNull($task->recurrence_type);
        $this->assertNull($task->recurrence_end_date);
    }

    #[Test]
    public function test_completing_recurring_task_creates_next_occurrence(): void
    {
        $task = Task::create([
            'task_number' => 'TASK-TEST-1',
            'date' => Carbon::today(),
            'created_by' => $this->user->id,
            'description' => 'Weekly floor cleaning',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addWeek(),
            'status' => 'pending',
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
        ]);

        TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $this->employee->id]);

        $this->actingAs($this->user)->patch(route('tasks.status', $task->id), ['status' => 'completed']);

        $task->refresh();
        $this->assertEquals('completed', $task->status);

        $nextTask = Task::where('parent_task_id', $task->id)->first();
        $this->assertNotNull($nextTask);
        $this->assertEquals('Weekly floor cleaning', $nextTask->description);
        $this->assertEquals('medium', $nextTask->priority);
        $this->assertTrue($nextTask->is_recurring);
        $this->assertEquals('weekly', $nextTask->recurrence_type);
        $this->assertEquals($task->deadline->copy()->addWeek()->toDateString(), $nextTask->deadline->toDateString());
        $this->assertEquals('pending', $nextTask->status);
    }

    #[Test]
    public function test_recurring_assignees_are_copied_to_next_task(): void
    {
        $task = Task::create([
            'task_number' => 'TASK-TEST-2',
            'date' => Carbon::today(),
            'created_by' => $this->user->id,
            'description' => 'Monthly deep clean',
            'priority' => 'high',
            'deadline' => Carbon::today()->addMonth(),
            'status' => 'in_progress',
            'is_recurring' => true,
            'recurrence_type' => 'monthly',
        ]);

        TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $this->employee->id]);

        $this->actingAs($this->user)->patch(route('tasks.status', $task->id), ['status' => 'completed']);

        $nextTask = Task::where('parent_task_id', $task->id)->first();
        $this->assertNotNull($nextTask);
        $this->assertTrue($nextTask->employees->pluck('id')->contains($this->employee->id));
    }

    #[Test]
    public function test_recurrence_stops_at_end_date(): void
    {
        $task = Task::create([
            'task_number' => 'TASK-TEST-3',
            'date' => Carbon::today(),
            'created_by' => $this->user->id,
            'description' => 'Short recurring task',
            'priority' => 'low',
            'deadline' => Carbon::today(),
            'status' => 'pending',
            'is_recurring' => true,
            'recurrence_type' => 'daily',
            'recurrence_end_date' => Carbon::yesterday(),
        ]);

        $this->actingAs($this->user)->patch(route('tasks.status', $task->id), ['status' => 'completed']);

        $nextTask = Task::where('parent_task_id', $task->id)->first();
        $this->assertNull($nextTask);
    }

    #[Test]
    public function test_toggle_complete_triggers_recurrence(): void
    {
        $task = Task::create([
            'task_number' => 'TASK-TEST-4',
            'date' => Carbon::today(),
            'created_by' => $this->user->id,
            'description' => 'Toggle recurring task',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDays(3),
            'status' => 'in_progress',
            'is_recurring' => true,
            'recurrence_type' => 'daily',
        ]);

        $this->actingAs($this->user)->patch(route('tasks.toggle-complete', $task->id));

        $task->refresh();
        $this->assertEquals('completed', $task->status);

        $nextTask = Task::where('parent_task_id', $task->id)->first();
        $this->assertNotNull($nextTask);
        $this->assertEquals($task->deadline->copy()->addDay()->toDateString(), $nextTask->deadline->toDateString());
    }

    #[Test]
    public function test_recurrence_service_calculates_correct_deadlines(): void
    {
        $service = app(TaskRecurrenceService::class);
        $base = Carbon::parse('2026-07-25');

        $this->assertEquals('2026-07-26', $service->calculateNextDeadline($base, 'daily')->toDateString());
        $this->assertEquals('2026-08-01', $service->calculateNextDeadline($base, 'weekly')->toDateString());
        $this->assertEquals('2026-08-08', $service->calculateNextDeadline($base, 'biweekly')->toDateString());
        $this->assertEquals('2026-08-25', $service->calculateNextDeadline($base, 'monthly')->toDateString());
    }

    #[Test]
    public function test_recurrence_creates_chained_history(): void
    {
        $task1 = Task::create([
            'task_number' => 'TASK-TEST-5',
            'date' => Carbon::today(),
            'created_by' => $this->user->id,
            'description' => 'Chain test',
            'priority' => 'high',
            'deadline' => Carbon::today()->addDays(2),
            'status' => 'pending',
            'is_recurring' => true,
            'recurrence_type' => 'biweekly',
        ]);

        $this->actingAs($this->user)->patch(route('tasks.status', $task1->id), ['status' => 'completed']);

        $task2 = Task::where('parent_task_id', $task1->id)->first();
        $this->assertNotNull($task2);

        $this->actingAs($this->user)->patch(route('tasks.status', $task2->id), ['status' => 'completed']);

        $task3 = Task::where('parent_task_id', $task2->id)->first();
        $this->assertNotNull($task3);
        $this->assertEquals($task1->deadline->copy()->addWeeks(4)->toDateString(), $task3->deadline->toDateString());

        $this->assertEquals($task1->id, $task2->parent_task_id);
        $this->assertEquals($task2->id, $task3->parent_task_id);
    }
}
