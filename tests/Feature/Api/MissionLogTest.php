<?php

namespace Tests\Feature\Api;

use App\Models\MissionLog;
use App\Models\Resource;
use App\Models\User;
use App\Notifications\MissionLogCreated;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class MissionLogTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'started_at' => Carbon::now(),
        ]);

        Passport::actingAs($user);
    }

    public function testIndex()
    {
        $user = auth()->user();

        $missionLog = MissionLog::factory()->create([
            'user_id' => $user->id,
        ]);

        $resource = Resource::factory()->create();

        $missionLog->resources()->attach($resource->id, [
            'quantity' => 5,
        ]);

        $missionLog->user->notify(
            new MissionLogCreated($missionLog->id)
        );

        $this->assertEquals($user->notifications()
            ->where('type', MissionLogCreated::class)
            ->count(), 1);

        $this->getJson('/api/mission-log')->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'energy',
                        'experience',
                        'created_at',
                        'resources' => [
                            [
                                'id',
                                'name',
                                'description',
                                'quantity',
                            ],
                        ],
                    ],
                ],
            ])->assertJson([
                'data' => [
                    [
                        'id' => $missionLog->id,
                        'energy' => $missionLog->energy,
                        'experience' => $missionLog->experience,
                        'created_at' => $missionLog->created_at,
                        'resources' => [
                            [
                                'id' => $resource->id,
                                'name' => $resource->translation('name'),
                                'description' => $resource->translation('description'),
                                'quantity' => 5,
                            ],
                        ],
                    ],
                ],
            ]);

        $this->assertEquals($user->notifications()
            ->where('type', MissionLogCreated::class)
            ->count(), 0);
    }
}
