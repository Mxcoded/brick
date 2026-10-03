<?php

namespace Modules\Tasks\Tests\Feature;

use App\Models\User;
use App\Notifications\TaskAssigned;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Models\TaskComment;
use Modules\Tasks\Services\TaskNumberGenerator;
use Modules\Tasks\Services\TaskService;
use Modules\Tasks\Support\TaskPermission;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The rules that used to be buried in TasksController. These are the behaviours
 * the old pages relied on, so they are pinned here against the service the
 * Filament resource now shares.
 */
class TaskServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $creator;

    protected Employee $creatorEmployee;

    protected User $other;

    protected Employee $otherEmployee;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->creator = User::factory()->create();
        $this->creatorEmployee = $this->makeEmployee($this->creator, 'Creator');
        $this->other = User::factory()->create();
        $this->otherEmployee = $this->makeEmployee($this->other, 'Other');
    }

    #[Test]
    public function it_creates_a_task_with_the_current_date_and_reference(): void
    {
        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Change the lobby bulbs',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDay()->toDateString(),
        ]);

        $this->assertSame(Carbon::today()->toDateString(), $task->date->toDateString());
        $this->assertStringStartsWith('TASK-'.Carbon::today()->format('dmy').'-', $task->task_number);
        $this->assertSame('pending', $task->status);
        $this->assertNull($task->completion_date);
        $this->assertTrue($task->updates()->where('action', 'created')->exists());
    }

    #[Test]
    public function it_assigns_the_creator_to_themselves_when_they_cannot_assign(): void
    {
        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Self assigned',
            'priority' => 'low',
            'deadline' => Carbon::today()->addDay()->toDateString(),
        ]);

        $this->assertTrue($task->employees->pluck('id')->contains($this->creatorEmployee->id));
    }

    #[Test]
    public function it_ignores_requested_assignees_without_the_assign_permission(): void
    {
        $this->grant($this->creator, TaskPermission::CREATE);

        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Trying to assign to someone else',
            'priority' => 'low',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'assignees' => [$this->otherEmployee->id],
        ]);

        $this->assertFalse($task->employees->pluck('id')->contains($this->otherEmployee->id));
        $this->assertTrue($task->employees->pluck('id')->contains($this->creatorEmployee->id));
    }

    #[Test]
    public function it_assigns_the_requested_people_when_the_user_can_assign(): void
    {
        $this->grant($this->creator, TaskPermission::ASSIGN);

        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Hand this to housekeeping',
            'priority' => 'high',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'assignees' => [$this->otherEmployee->id],
        ]);

        $this->assertTrue($task->employees->pluck('id')->contains($this->otherEmployee->id));
        $this->assertFalse($task->employees->pluck('id')->contains($this->creatorEmployee->id));
    }

    #[Test]
    public function it_notifies_assignees_that_have_a_login(): void
    {
        $this->grant($this->creator, TaskPermission::ASSIGN);

        app(TaskService::class)->create($this->creator, [
            'description' => 'Notify housekeeping',
            'priority' => 'high',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'assignees' => [$this->otherEmployee->id],
        ]);

        Notification::assertSentTo($this->other, TaskAssigned::class);
    }

    #[Test]
    public function only_the_creator_can_edit_a_task(): void
    {
        $task = $this->taskFor($this->creator);

        $this->expectException(AuthorizationException::class);

        app(TaskService::class)->update($task, $this->other, [
            'description' => 'Not mine to change',
            'priority' => 'high',
            'deadline' => Carbon::today()->addDay()->toDateString(),
        ]);
    }

    #[Test]
    public function editing_records_every_changed_field_in_one_audit_entry(): void
    {
        $task = $this->taskFor($this->creator, [
            'description' => 'Original',
            'priority' => 'low',
        ]);

        app(TaskService::class)->update($task, $this->creator, [
            'description' => 'Rewritten',
            'priority' => 'high',
            'deadline' => Carbon::today()->addDays(5)->toDateString(),
        ]);

        $updates = $task->updates()->where('action', 'updated')->get();
        $this->assertCount(1, $updates);

        $changes = $updates->first()->changes;

        // Asserted key by key: the changes column is native JSON, and MySQL
        // reorders object keys by length on the way in, so assertSame on the
        // whole array would fail on ordering rather than on content.
        $this->assertSame('Original', $changes['description']['from']);
        $this->assertSame('Rewritten', $changes['description']['to']);
        $this->assertSame('low', $changes['priority']['from']);
        $this->assertSame('high', $changes['priority']['to']);
        $this->assertArrayHasKey('deadline', $changes);
    }

    #[Test]
    public function editing_keeps_field_and_assignee_changes_in_the_same_audit_entry(): void
    {
        $this->grant($this->creator, TaskPermission::ASSIGN);

        $task = $this->taskFor($this->creator, ['description' => 'Before']);

        app(TaskService::class)->update($task, $this->creator, [
            'description' => 'After',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'assignees' => [$this->otherEmployee->id],
        ]);

        $update = $task->updates()->where('action', 'updated')->latest('id')->first();

        $this->assertNotNull($update);
        $this->assertArrayHasKey('description', $update->changes);
        $this->assertArrayHasKey('assignees', $update->changes);
        $this->assertSame([$this->otherEmployee->name], $update->changes['assignees']['to']);
    }

    #[Test]
    public function an_assignee_can_change_status_but_not_the_content(): void
    {
        $this->grant($this->creator, TaskPermission::ASSIGN);

        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Assigned out',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'assignees' => [$this->otherEmployee->id],
        ]);

        $task = app(TaskService::class)->changeStatus($task, $this->other, 'in_progress');
        $this->assertSame('in_progress', $task->status);

        $this->expectException(AuthorizationException::class);

        app(TaskService::class)->update($task, $this->other, [
            'description' => 'Rewritten by an assignee',
            'priority' => 'low',
            'deadline' => Carbon::today()->addDay()->toDateString(),
        ]);
    }

    #[Test]
    public function a_stranger_can_neither_change_nor_comment(): void
    {
        $task = $this->taskFor($this->creator);

        try {
            app(TaskService::class)->changeStatus($task, $this->other, 'completed');
            $this->fail('An unrelated user should not be able to change the status.');
        } catch (AuthorizationException) {
            // expected
        }

        $this->expectException(AuthorizationException::class);

        app(TaskService::class)->comment($task, $this->other, 'Not my task');
    }

    #[Test]
    public function completing_a_task_stamps_the_completion_date_and_reopening_clears_it(): void
    {
        $task = $this->taskFor($this->creator);

        $task = app(TaskService::class)->changeStatus($task, $this->creator, 'completed');
        $this->assertNotNull($task->completion_date);
        $this->assertSame(Carbon::today()->toDateString(), $task->completion_date->toDateString());

        $task = app(TaskService::class)->changeStatus($task, $this->creator, 'pending');
        $this->assertNull($task->completion_date);
    }

    #[Test]
    public function moving_back_into_progress_keeps_the_original_completion_date(): void
    {
        $task = $this->taskFor($this->creator);

        $task = app(TaskService::class)->changeStatus($task, $this->creator, 'completed');
        $task = app(TaskService::class)->changeStatus($task, $this->creator, 'in_progress');

        $this->assertNotNull($task->completion_date);
    }

    #[Test]
    public function toggling_walks_through_the_workflow(): void
    {
        $task = $this->taskFor($this->creator);
        $service = app(TaskService::class);

        $this->assertSame('in_progress', $service->toggleComplete($task, $this->creator)->status);
        $this->assertSame('completed', $service->toggleComplete($task, $this->creator)->status);
        $this->assertSame('pending', $service->toggleComplete($task, $this->creator)->status);
    }

    #[Test]
    public function only_the_creator_can_delete(): void
    {
        $task = $this->taskFor($this->creator);

        $this->expectException(AuthorizationException::class);

        app(TaskService::class)->delete($task, $this->other);
    }

    #[Test]
    public function a_bulk_status_change_skips_tasks_the_user_does_not_own(): void
    {
        $mine = $this->taskFor($this->creator, ['description' => 'Mine']);
        $theirs = $this->taskFor($this->other, ['description' => 'Theirs']);

        $changed = app(TaskService::class)->bulkChangeStatus(
            $this->creator,
            [$mine->id, $theirs->id],
            'completed',
        );

        $this->assertSame(1, $changed);
        $this->assertSame('completed', $mine->fresh()->status);
        $this->assertSame('pending', $theirs->fresh()->status);
    }

    #[Test]
    public function a_bulk_assign_skips_duplicate_assignments(): void
    {
        $task = $this->taskFor($this->creator);

        $first = app(TaskService::class)->bulkAssign($this->creator, [$task->id], $this->otherEmployee->id);
        $second = app(TaskService::class)->bulkAssign($this->creator, [$task->id], $this->otherEmployee->id);

        $this->assertSame(1, $first);
        $this->assertSame(0, $second);
        $this->assertSame(1, $task->employees()->count());
    }

    #[Test]
    public function commenting_records_the_comment_and_its_preview(): void
    {
        $task = $this->taskFor($this->creator);

        app(TaskService::class)->comment($task, $this->creator, 'On it, will finish by noon.');

        $this->assertSame(1, TaskComment::where('task_id', $task->id)->count());

        $update = $task->updates()->where('action', 'comment_added')->first();
        $this->assertNotNull($update);
        $this->assertSame('On it, will finish by noon.', $update->changes['comment_preview']);
    }

    #[Test]
    public function it_shows_raised_and_assigned_tasks_without_duplicating_own_tasks(): void
    {
        $mine = $this->taskFor($this->creator, ['description' => 'Mine']);

        // Also assigned to me, so it must not appear twice.
        TaskAssignment::create(['task_id' => $mine->id, 'employee_id' => $this->creatorEmployee->id]);

        $assignedToMe = $this->taskFor($this->other, ['description' => 'Someone else raised this']);
        TaskAssignment::create(['task_id' => $assignedToMe->id, 'employee_id' => $this->creatorEmployee->id]);

        $unrelated = $this->taskFor($this->other, ['description' => 'Not mine at all']);

        $descriptions = app(TaskService::class)->visibleTo($this->creator)->pluck('description')->all();

        $this->assertContains('Mine', $descriptions);
        $this->assertContains('Someone else raised this', $descriptions);
        $this->assertNotContains('Not mine at all', $descriptions);
        $this->assertCount(2, $descriptions);
    }

    #[Test]
    public function it_shows_nothing_assigned_to_a_user_with_no_employee_record(): void
    {
        $this->taskFor($this->creator, ['description' => 'Mine']);

        $theirs = $this->taskFor($this->other, ['description' => 'Someone else raised this']);
        TaskAssignment::create(['task_id' => $theirs->id, 'employee_id' => $this->creatorEmployee->id]);

        // A user with no employee row cannot be assigned anything, so the
        // assigned list must come back empty rather than falling back to every
        // task other people raised.
        $orphan = User::factory()->create();
        $orphan->givePermissionTo(TaskPermission::all());

        $this->assertNull(Employee::where('user_id', $orphan->id)->first());

        $split = app(TaskService::class)->splitByRole($orphan);

        $this->assertSame(0, $split['created']->count());
        $this->assertSame(0, $split['assigned']->count());
        $this->assertSame(0, app(TaskService::class)->visibleTo($orphan)->count());
    }

    #[Test]
    public function stats_only_count_what_the_user_can_see(): void
    {
        $mine = $this->taskFor($this->creator, ['description' => 'Mine']);
        $mine->update(['status' => 'in_progress']);

        $theirs = $this->taskFor($this->other, ['description' => 'Theirs']);

        $stats = app(TaskService::class)->statsFor($this->creator);

        $this->assertSame(1, $stats['total']);
        $this->assertSame(1, $stats['in_progress']);
        $this->assertSame(0, $stats['pending']);
        $this->assertSame(2, Task::count());
    }

    #[Test]
    public function overdue_stats_exclude_completed_tasks(): void
    {
        $task = $this->taskFor($this->creator, ['deadline' => Carbon::yesterday()->toDateString()]);

        $this->assertSame(1, app(TaskService::class)->statsFor($this->creator)['overdue']);

        $task->update(['status' => 'completed']);

        $this->assertSame(0, app(TaskService::class)->statsFor($this->creator)['overdue']);
    }

    #[Test]
    public function references_do_not_reuse_a_deleted_number(): void
    {
        $generator = app(TaskNumberGenerator::class);

        $first = $generator->next(Carbon::today());
        Task::create([
            'task_number' => $first,
            'date' => Carbon::today(),
            'created_by' => $this->creator->id,
            'description' => 'First',
            'priority' => 'low',
            'deadline' => Carbon::today(),
        ]);

        // Deleting must not hand the same reference out again, which a
        // count()+1 implementation would do.
        Task::where('task_number', $first)->delete();

        $this->assertNotSame($first, $generator->next(Carbon::today()));
    }

    #[Test]
    public function references_continue_past_the_last_one_issued(): void
    {
        $service = app(TaskService::class);

        $first = $service->generateTaskNumber();
        $second = $service->generateTaskNumber();

        $this->assertNotSame($first, $second);
        $this->assertSame(
            (int) substr($second, -4),
            (int) substr($first, -4) + 1,
        );
    }

    #[Test]
    public function completing_a_recurring_task_creates_the_next_occurrence(): void
    {
        $this->grant($this->creator, TaskPermission::ASSIGN);

        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Daily inspection',
            'priority' => 'medium',
            'deadline' => Carbon::today()->toDateString(),
            'assignees' => [$this->otherEmployee->id],
            'is_recurring' => true,
            'recurrence_type' => 'daily',
        ]);

        app(TaskService::class)->changeStatus($task, $this->creator, 'completed');

        $next = Task::where('parent_task_id', $task->id)->first();

        $this->assertNotNull($next);
        $this->assertNotSame($task->task_number, $next->task_number);
        $this->assertTrue($next->employees->pluck('id')->contains($this->otherEmployee->id));
        $this->assertTrue($next->updates()->where('action', 'created_from_recurring')->exists());
    }

    #[Test]
    public function turning_recurrence_off_clears_the_recurrence_fields(): void
    {
        $this->grant($this->creator, TaskPermission::ASSIGN);

        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Was repeating',
            'priority' => 'low',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'is_recurring' => true,
            'recurrence_type' => 'weekly',
            'recurrence_end_date' => Carbon::today()->addMonth()->toDateString(),
        ]);

        $task = app(TaskService::class)->update($task, $this->creator, [
            'description' => 'Was repeating',
            'priority' => 'low',
            'deadline' => Carbon::today()->addDay()->toDateString(),
            'is_recurring' => false,
        ])['task'];

        $this->assertFalse($task->is_recurring);
        $this->assertNull($task->recurrence_type);
        $this->assertNull($task->recurrence_end_date);
    }

    #[Test]
    public function the_audit_trail_keeps_a_chronological_record(): void
    {
        // Raised through the service, so the "created" entry exists too.
        $task = app(TaskService::class)->create($this->creator, [
            'description' => 'Audited task',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDay()->toDateString(),
        ]);

        $service = app(TaskService::class);
        $service->changeStatus($task, $this->creator, 'in_progress');
        $service->comment($task, $this->creator, 'Started');

        $actions = $task->updates()->orderBy('id')->pluck('action')->all();

        // Creating self-assigns the raiser, so the assignment is part of the
        // opening audit entry rather than a later change.
        $this->assertSame(
            ['created', 'assignees_changed', 'status_changed', 'comment_added'],
            $actions
        );

        foreach ($task->updates as $update) {
            $this->assertSame($this->creator->id, $update->user_id);
        }
    }

    protected function taskFor(User $user, array $overrides = []): Task
    {
        return Task::create(array_merge([
            'task_number' => 'TASK-TEST-'.uniqid(),
            'date' => Carbon::today(),
            'created_by' => $user->id,
            'description' => 'A task',
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDay(),
            'status' => 'pending',
        ], $overrides));
    }

    protected function makeEmployee(User $user, string $name): Employee
    {
        static $sequence = 0;
        $sequence++;

        return Employee::create([
            'name' => $name.' '.$sequence,
            'email' => 'task_'.uniqid().'@brickspoint.com',
            'phone_number' => '080'.random_int(10000000, 99999999),
            'position' => 'Staff',
            'department' => 'Operations',
            'branch_name' => 'Asokoro',
            'status' => 'approved',
            'user_id' => $user->id,
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
    }

    protected function grant(User $user, string $permission): void
    {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
    }
}
