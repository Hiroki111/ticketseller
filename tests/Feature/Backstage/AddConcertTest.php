<?php

namespace Tests\Feature\Backstage;

use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function promoters_can_view_the_add_concert_form()
    {
        $this->withoutExceptionHandling();
        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->get('/backstage/concerts/new');
        $response->assertStatus(200);
    }

    /** @test */
    public function guests_cannot_view_the_add_concert_form()
    {
        $this->expectException(AuthenticationException::class);
        $this->withoutExceptionHandling();
        $response = $this->get('/backstage/concerts/new');
        $response->assertRedirect('/login');
    }
}
