<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogoutIdleUsersTest extends TestCase
{
    use DatabaseTransactions;

    private function makeSession(string $id, int $userId): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $userId,
            'payload' => '',
            'last_activity' => time(),
        ]);
    }

    private function makeLoginLog(User $user, string $sessionId, $lastActivity): UserLoginLog
    {
        $log = UserLoginLog::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => '127.0.0.1',
            'status' => 'success',
            'logged_in_at' => now()->subHours(5),
        ]);

        $log->forceFill(['last_activity_at' => $lastActivity])->save();

        return $log;
    }

    public function test_idle_sessions_are_logged_out_and_destroyed()
    {
        $user = User::factory()->create();

        $idleId = 'idle-session-1';
        $this->makeSession($idleId, $user->id);
        $idle = $this->makeLoginLog($user, $idleId, now()->subHours(4));

        $activeId = 'active-session-1';
        $this->makeSession($activeId, $user->id);
        $active = $this->makeLoginLog($user, $activeId, now()->subMinutes(5));

        $this->artisan('auth:logout-idle', ['--hours' => 3])
            ->assertSuccessful();

        // Idle session: marked logged out and its session row destroyed.
        $this->assertNotNull($idle->fresh()->logged_out_at);
        $this->assertDatabaseMissing('sessions', ['id' => $idleId]);

        // Active session: untouched.
        $this->assertNull($active->fresh()->logged_out_at);
        $this->assertDatabaseHas('sessions', ['id' => $activeId]);
    }

    public function test_boundary_is_respected()
    {
        $user = User::factory()->create();

        $boundaryId = 'boundary-session';
        $this->makeSession($boundaryId, $user->id);
        $boundary = $this->makeLoginLog($user, $boundaryId, now()->subHours(3));

        $this->artisan('auth:logout-idle', ['--hours' => 3])
            ->assertSuccessful();

        // Exactly 3 hours idle is kept (strictly more than is logged out).
        $this->assertNull($boundary->fresh()->logged_out_at);
        $this->assertDatabaseHas('sessions', ['id' => $boundaryId]);
    }
}
