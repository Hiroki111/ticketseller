<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PublishConcertController extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function a_promoter_can_publish_their_own_concert()
    {
        //$this->withoutExceptionHandling();
        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->states('unpublished')->create([
            'user_id'         => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/backstage/concerts');
        $concert = $concert->fresh();
        $this->assertTrue($concert->isPublished());
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function a_concert_can_only_be_published_once()
    {
        //$this->withoutExceptionHandling();
        $user    = factory(User::class)->create();
        $concert = \ConcertFactory::createPublished([
            'user_id'         => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(422);
        $concert->fresh();
        $this->assertEquals(3, $concert->ticketsRemaining());
    }
}
