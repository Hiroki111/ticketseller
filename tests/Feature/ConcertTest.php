<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    /** @test */
    public function user_cannot_view_unpublished_concert_listings()
    {
        $concert = factory(Concert::class)->make([
            'published_at' => null,
        ]);

        $response = $this->get('/concerts/' . $concert->id);
        $response->assertStatus(404);
    }
}
