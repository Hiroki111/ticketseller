<?php

namespace Tests\Feature;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function user_cannot_view_unpublished_concert_listings()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();

        $response = $this->get('/concerts/' . $concert->id);
        $response->assertStatus(404);
    }
}
