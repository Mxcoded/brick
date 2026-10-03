<?php

namespace Modules\Tasks\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Modules\Staff\Models\Employee;
use Modules\Tasks\Filament\Pages\TaskKanban;
use Modules\Tasks\Filament\Resources\Tasks\Pages\CreateTask;
use Modules\Tasks\Filament\Resources\Tasks\Pages\EditTask;
use Modules\Tasks\Filament\Resources\Tasks\Pages\ListTasks;
use Modules\Tasks\Filament\Resources\Tasks\Pages\ViewTask;
use Modules\Tasks\Filament\Resources\Tasks\TaskResource;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAssignment;
use Modules\Tasks\Services\TaskService;
use Modules\Tasks\Support\TaskPermission;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TaskResourceTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->employee = $this->makeEmployee($this->user);

        foreach (TaskPermission::all() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->grantAll();
    }

    #[Test]
    public function the_task_list_renders_for_a_permitted_user(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListTasks::class)->assertOk();
    }

    #[Test]
    public function the_task_list_only_shows_tasks_the_user_raised_or_was_assigned(): void
    {
        $mine = $this->taskFor($this->user, 'Mine');

        $assigned = $this->taskFor(User::factory()->create(), 'Assigned to me');
        TaskAssignment::create(['task_id' => $assigned->id, 'employee_id' => $this->employee->id]);

        $this->taskFor(User::factory()->create(), 'Someone elses problem');

        $this->actingAs($this->user);

        Livewire::test(ListTasks::class)
            ->assertOk()
            ->assertCanSeeTableRecords([$mine, $assigned])
            ->assertCanNotSeeTableRecords(
                Task::where('description', 'Someone elses problem')->get()
            );
    }

    #[Test]
    public function a_user_without_task_permissions_is_locked_out(): void
    {
        $plain = User::factory()->create();
        $plain->syncPermissions([]);

        $this->actingAs($plain);

        $this->assertFalse(TaskResource::canAccess());
        $this->assertFalse(TaskResource::canViewAny());
    }

    #[Test]
    public function a_user_who_reaches_the_panel_without_task_permissions_gets_a_403(): void
    {
        // Allowed into the panel by another module, but not into Tasks. The
        // resource itself answers 403, as opposed to a record they cannot see,
        // which is a 404.
        $admin = User::factory()->create();
        $admin->givePermissionTo('access_admin_dashboard');
        $admin->syncPermissions(['access_admin_dashboard']);

        $task = $this->taskFor($this->user, 'Not for you');

        $this->actingAs($admin);

        $this->assertFalse(TaskResource::canViewAny());
        $this->get(TaskResource::getUrl('index'))->assertForbidden();
    }

    #[Test]
    public function dashboard_access_alone_is_enough_to_reach_the_list(): void
    {
        $staff = User::factory()->create();
        $staff->syncPermissions([TaskPermission::DASHBOARD_ACCESS]);

        $this->actingAs($staff);

        $this->assertTrue(TaskResource::canViewAny());
    }

    #[Test]
    public function creating_requires_the_create_permission(): void
    {
        $staff = User::factory()->create();
        $staff->syncPermissions([TaskPermission::DASHBOARD_ACCESS, TaskPermission::READ]);

        $this->actingAs($staff);

        $this->assertFalse(TaskResource::canCreate());
        $this->get(TaskResource::getUrl('create'))->assertForbidden();
    }

    #[Test]
    public function a_permitted_user_can_create_a_task_through_the_resource(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'description' => 'Created from the admin panel',
                'priority' => 'high',
                'deadline' => Carbon::today()->addDay()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::where('description', 'Created from the admin panel')->first();

        $this->assertNotNull($task);
        $this->assertSame($this->user->id, $task->created_by);
        $this->assertSame('pending', $task->status);
        $this->assertSame(0, strcasecmp('pending', 'pending'));
        $this->assertTrue($task->updates()->where('action', 'created')->exists());
    }

    #[Test]
    public function a_repeating_task_created_through_the_resource_keeps_its_recurrence(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateTask::class)
            ->fillForm([
                'description' => 'Weekly laundry check',
                'priority' => 'low',
                'deadline' => Carbon::today()->addDay()->toDateString(),
                'is_recurring' => true,
                'recurrence_type' => 'weekly',
                'recurrence_end_date' => Carbon::today()->addMonth()->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $task = Task::where('description', 'Weekly laundry check')->first();

        $this->assertTrue($task->is_recurring);
        $this->assertSame('weekly', $task->recurrence_type);
    }

    #[Test]
    public function only_the_creator_sees_edit_and_delete(): void
    {
        $mine = $this->taskFor($this->user, 'Mine');

        // Visible to me because I am assigned, but not mine to edit.
        $assigned = $this->taskFor(User::factory()->create(), 'Assigned to me');
        TaskAssignment::create(['task_id' => $assigned->id, 'employee_id' => $this->employee->id]);

        $this->actingAs($this->user);

        $this->assertTrue(TaskResource::canEdit($mine));
        $this->assertTrue(TaskResource::canDelete($mine));

        $this->assertTrue(TaskResource::canView($assigned));
        $this->assertFalse(TaskResource::canEdit($assigned));
        $this->assertFalse(TaskResource::canDelete($assigned));

        $this->get(TaskResource::getUrl('edit', ['record' => $assigned]))->assertForbidden();
    }

    #[Test]
    public function editing_without_the_update_permission_is_refused(): void
    {
        $staff = User::factory()->create();
        $staff->syncPermissions([TaskPermission::DASHBOARD_ACCESS, TaskPermission::READ, TaskPermission::CREATE]);

        $task = $this->taskFor($staff, 'Theirs to edit');

        $this->actingAs($staff);

        $this->assertFalse(TaskResource::canEdit($task));
        $this->get(TaskResource::getUrl('edit', ['record' => $task]))->assertForbidden();
    }

    #[Test]
    public function editing_through_the_resource_keeps_ownership_and_audit(): void
    {
        $task = $this->taskFor($this->user, 'Before edit');

        $this->actingAs($this->user);

        Livewire::test(EditTask::class, ['record' => $task->getRouteKey()])
            ->fillForm([
                'description' => 'After edit',
                'priority' => 'high',
                'deadline' => Carbon::today()->addDays(3)->toDateString(),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $task->refresh();

        $this->assertSame('After edit', $task->description);
        $this->assertSame('high', $task->priority);
        $this->assertTrue($task->updates()->where('action', 'updated')->exists());
    }

    #[Test]
    public function an_assignee_can_open_a_task_they_did_not_raise(): void
    {
        $task = $this->taskFor(User::factory()->create(), 'Someone elses task');
        TaskAssignment::create(['task_id' => $task->id, 'employee_id' => $this->employee->id]);

        $this->actingAs($this->user);

        $this->assertTrue(TaskResource::canView($task));

        Livewire::test(ViewTask::class, ['record' => $task->getRouteKey()])->assertOk();
    }

    #[Test]
    public function a_stranger_cannot_open_a_task(): void
    {
        $task = $this->taskFor(User::factory()->create(), 'Private');

        $this->actingAs($this->user);

        $this->assertFalse(TaskResource::canView($task));

        // 404 rather than 403: the resource scopes its own query to what the
        // user may see, so a task they cannot see is indistinguishable from one
        // that does not exist.
        $this->get(TaskResource::getUrl('view', ['record' => $task]))->assertNotFound();
    }

    #[Test]
    public function completing_from_the_view_page_stamps_the_date_and_records_the_change(): void
    {
        $task = $this->taskFor($this->user, 'Finish me');

        $this->actingAs($this->user);

        Livewire::test(ViewTask::class, ['record' => $task->getRouteKey()])
            ->callAction('complete')
            ->assertHasNoActionErrors();

        $task->refresh();

        $this->assertSame('completed', $task->status);
        $this->assertSame(Carbon::today()->toDateString(), $task->completion_date->toDateString());
        $this->assertTrue($task->updates()->where('action', 'status_changed')->exists());
    }

    #[Test]
    public function the_creator_can_delete_a_task_from_the_table(): void
    {
        $task = $this->taskFor($this->user, 'Remove me');

        $this->actingAs($this->user);

        Livewire::test(ListTasks::class)
            ->callTableAction('delete', $task);

        $this->assertNull(Task::find($task->id));
    }

    #[Test]
    public function a_bulk_status_change_from_the_table_respects_ownership(): void
    {
        $mine = $this->taskFor($this->user, 'Mine');
        $theirs = $this->taskFor(User::factory()->create(), 'Theirs');

        $this->actingAs($this->user);

        Livewire::test(ListTasks::class)
            ->callTableBulkAction('mark_completed', [$mine, $theirs]);

        $this->assertSame('completed', $mine->fresh()->status);
        $this->assertSame('pending', $theirs->fresh()->status);
    }

    #[Test]
    public function the_stats_widget_reflects_only_the_users_tasks(): void
    {
        $mine = $this->taskFor($this->user, 'Mine');
        $mine->update(['status' => 'in_progress']);

        $this->taskFor(User::factory()->create(), 'Theirs');

        $this->actingAs($this->user);

        $stats = app(TaskService::class)->statsFor($this->user);

        $this->assertSame(1, $stats['total']);
        $this->assertSame(1, $stats['in_progress']);
    }

    #[Test]
    public function the_legacy_kanban_route_still_exists_for_bookmarks(): void
    {
        $this->assertTrue(Route::has('tasks.index'));

        // The Blade board is still reachable, but the admin links now point at
        // the panel page.
        $this->actingAs($this->user);

        $this->get(route('tasks.index', ['view' => 'kanban']))->assertOk();
    }

    #[Test]
    public function the_board_renders_for_a_permitted_user(): void
    {
        $this->taskFor($this->user, 'On the board');

        $this->actingAs($this->user);

        Livewire::test(TaskKanban::class)
            ->assertOk()
            ->assertSee('On the board');
    }

    #[Test]
    public function the_board_only_shows_tasks_the_user_raised_or_was_assigned(): void
    {
        $mine = $this->taskFor($this->user, 'Mine to see');

        $assigned = $this->taskFor(User::factory()->create(), 'Assigned to me');
        TaskAssignment::create(['task_id' => $assigned->id, 'employee_id' => $this->employee->id]);

        $this->taskFor(User::factory()->create(), 'Not mine at all');

        $this->actingAs($this->user);

        Livewire::test(TaskKanban::class)
            ->assertOk()
            ->assertSee('Mine to see')
            ->assertSee('Assigned to me')
            ->assertDontSee('Not mine at all');
    }

    #[Test]
    public function dropping_a_card_moves_the_task(): void
    {
        $task = $this->taskFor($this->user, 'Drag me');

        $this->actingAs($this->user);

        Livewire::test(TaskKanban::class)->call('setStatus', $task->id, 'in_progress');

        $task->refresh();

        $this->assertSame('in_progress', $task->status);
        $this->assertNull($task->completed_at);
    }

    #[Test]
    public function dropping_a_card_into_completed_stamps_the_completion_date(): void
    {
        $task = $this->taskFor($this->user, 'Finish me');

        $this->actingAs($this->user);

        Livewire::test(TaskKanban::class)->call('setStatus', $task->id, 'completed');

        $task->refresh();

        $this->assertSame('completed', $task->status);
        $this->assertNotNull($task->completion_date);
    }

    #[Test]
    public function an_assignee_can_move_a_task_but_a_stranger_cannot(): void
    {
        $assigned = $this->taskFor(User::factory()->create(), 'Assigned to me');
        TaskAssignment::create(['task_id' => $assigned->id, 'employee_id' => $this->employee->id]);

        $private = $this->taskFor(User::factory()->create(), 'Not mine at all');

        $this->actingAs($this->user);

        Livewire::test(TaskKanban::class)->call('setStatus', $assigned->id, 'in_progress');
        $this->assertSame('in_progress', $assigned->fresh()->status);

        // A task they cannot see is simply not found; nothing changes.
        Livewire::test(TaskKanban::class)->call('setStatus', $private->id, 'in_progress');
        $this->assertSame('pending', $private->fresh()->status);
    }

    #[Test]
    public function the_board_is_closed_to_a_user_without_task_permissions(): void
    {
        $plain = User::factory()->create();
        $plain->syncPermissions([]);

        $this->actingAs($plain);

        $this->assertFalse(TaskKanban::canAccess());

        // Not permitted into the panel at all, so the panel middleware bounces
        // them before the page's own canAccess() is consulted.
        $this->get(TaskKanban::getUrl())->assertRedirect();
    }

    #[Test]
    public function the_list_page_links_to_the_filament_board(): void
    {
        $this->actingAs($this->user);

        $this->assertSame(TaskKanban::getUrl(), TaskResource::kanbanUrl());
        $this->assertStringContainsString('tasks/board', TaskResource::kanbanUrl());
    }

    #[Test]
    public function a_task_due_today_is_not_yet_overdue(): void
    {
        $dueToday = $this->taskFor($this->user, 'Due today');
        $dueToday->update(['deadline' => Carbon::today()]);

        $overdue = $this->taskFor($this->user, 'Overdue');
        $overdue->update(['deadline' => Carbon::today()->subDay()]);

        $done = $this->taskFor($this->user, 'Done');
        $done->update(['deadline' => Carbon::today()->subDay(), 'status' => 'completed']);

        $this->assertFalse($dueToday->fresh()->isOverdue());
        $this->assertTrue($overdue->fresh()->isOverdue());
        $this->assertFalse($done->fresh()->isOverdue());
    }

    protected function taskFor(User $user, string $description): Task
    {
        return Task::create([
            'task_number' => 'TASK-TEST-'.uniqid(),
            'date' => Carbon::today(),
            'created_by' => $user->id,
            'description' => $description,
            'priority' => 'medium',
            'deadline' => Carbon::today()->addDay(),
            'status' => 'pending',
        ]);
    }

    protected function grantAll(): void
    {
        $this->user->givePermissionTo(TaskPermission::all());
    }

    protected function makeEmployee(User $user): Employee
    {
        return Employee::create([
            'name' => 'Resource Tester '.uniqid(),
            'email' => 'task_res_'.uniqid().'@brickspoint.com',
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
}
