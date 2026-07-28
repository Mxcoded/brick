<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PruneActivityLogsTest extends TestCase
{
    use DatabaseTransactions;

    private function makeLog(int $daysAgo): UserActivityLog
    {
        $user = User::factory()->create();

        $log = UserActivityLog::create([
            'user_id' => $user->id,
            'action' => 'page_view',
            'description' => 'seed',
            'method' => 'GET',
            'url' => '/',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
        ]);

        $log->forceFill(['created_at' => now()->subDays($daysAgo)])->save();

        return $log;
    }

    public function test_prune_deletes_only_logs_older_than_given_days()
    {
        $old = $this->makeLog(10);
        $recent = $this->makeLog(1);

        $this->artisan('activity-logs:prune', ['--days' => 7])
            ->assertSuccessful();

        $this->assertDatabaseMissing('user_activity_logs', ['id' => $old->id]);
        $this->assertDatabaseHas('user_activity_logs', ['id' => $recent->id]);
    }

    public function test_default_is_recommended_ninety_days()
    {
        $old = $this->makeLog(91);
        // Add 1 second so the boundary log is clearly at/after the cutoff,
        // avoiding microsecond timing differences between makeLog and the command.
        $boundary = $this->makeLog(90);
        $boundary->forceFill(['created_at' => now()->subDays(90)->addSecond()])->save();

        $this->artisan('activity-logs:prune')->assertSuccessful();

        $this->assertDatabaseMissing('user_activity_logs', ['id' => $old->id]);
        // Exactly 90 days old is kept (strictly older than is pruned).
        $this->assertDatabaseHas('user_activity_logs', ['id' => $boundary->id]);
    }
}
