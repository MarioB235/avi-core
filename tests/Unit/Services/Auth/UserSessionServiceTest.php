<?php

namespace Tests\Unit\Services\Auth;

use App\Models\Empresa;
use App\Models\User;
use App\Services\Auth\UserSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserSessionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'database']);
    }

    public function test_invalidate_all_for_user_removes_database_sessions(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            [
                'id' => 'session-a',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session-b',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        app(UserSessionService::class)->invalidateAllForUser($user);

        $this->assertSame(0, DB::table('sessions')->where('user_id', $user->id)->count());
    }

    public function test_invalidate_other_sessions_keeps_current_session(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            [
                'id' => 'session-current',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session-other',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        app(UserSessionService::class)->invalidateOtherSessionsForUser($user, 'session-current');

        $this->assertDatabaseHas('sessions', ['id' => 'session-current']);
        $this->assertDatabaseMissing('sessions', ['id' => 'session-other']);
    }

    public function test_invalidate_all_for_empresa_removes_sessions_of_company_users(): void
    {
        $empresa = Empresa::factory()->create();
        $userA = User::factory()->create(['empresa_id' => $empresa->id]);
        $userB = User::factory()->create(['empresa_id' => $empresa->id]);
        $otherUser = User::factory()->create();

        DB::table('sessions')->insert([
            [
                'id' => 'session-a',
                'user_id' => $userA->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session-b',
                'user_id' => $userB->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'session-other',
                'user_id' => $otherUser->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'test',
                'payload' => 'payload',
                'last_activity' => now()->timestamp,
            ],
        ]);

        app(UserSessionService::class)->invalidateAllForEmpresa($empresa->id);

        $this->assertSame(0, DB::table('sessions')->whereIn('user_id', [$userA->id, $userB->id])->count());
        $this->assertDatabaseHas('sessions', ['id' => 'session-other']);
    }

    public function test_invalidate_all_is_no_op_when_session_driver_is_not_database(): void
    {
        config(['session.driver' => 'file']);

        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'session-persist',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        app(UserSessionService::class)->invalidateAllForUser($user);

        $this->assertDatabaseHas('sessions', ['id' => 'session-persist']);
    }
}
