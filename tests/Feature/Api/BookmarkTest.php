<?php

namespace Tests\Feature\Api;

use App\Models\Bookmark;
use App\Models\Star;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BookmarkTest extends TestCase
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
        $star = Star::factory()->create();

        $bookmark = Bookmark::factory()->create([
            'name' => 'Favorite',
            'user_id' => auth()->user()->id,
            'star_id' => $star->id,
        ]);

        $this->getJson('/api/bookmark')->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
                'data' => [
                    [
                        'id',
                        'name',
                        'x',
                        'y',
                        'created_at',
                    ],
                ],
            ])->assertJson([
                'data' => [
                    [
                        'id' => $bookmark->id,
                        'name' => $bookmark->name,
                        'x' => $bookmark->star->x,
                        'y' => $bookmark->star->y,
                        'created_at' => $bookmark->created_at,
                    ],
                ],
            ]);
    }

    public function testStore()
    {
        $star = Star::factory()->create();

        $bookmark = Bookmark::factory()->create([
            'user_id' => auth()->user()->id,
            'star_id' => $star->id,
        ]);

        $this->post('/api/bookmark/10')
            ->assertStatus(404);

        $this->post('/api/bookmark/not-id')
            ->assertStatus(404);

        $this->post("/api/bookmark/{$star->id}")
            ->assertStatus(200);

        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->id,
        ]);
    }

    public function testDestroy()
    {
        $bookmark = Bookmark::factory()->create([
            'user_id' => auth()->user()->id,
        ]);

        $this->delete('/api/bookmark/10')
            ->assertStatus(404);

        $this->delete('/api/bookmark/not-id')
            ->assertStatus(404);

        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->id,
        ]);

        $this->delete("/api/bookmark/{$bookmark->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('bookmarks', [
            'id' => $bookmark->id,
        ]);
    }
}
