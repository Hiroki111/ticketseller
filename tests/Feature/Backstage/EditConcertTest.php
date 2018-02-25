<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional info',
            'date'                   => '2018-12-11',
            'time'                   => '8:00pm',
            'venue'                  => 'New venue',
            'address'                => 'New address',
            'suburb'                 => 'New suburb',
            'state'                  => 'New state',
            'zip'                    => '9999',
            'ticket_price'           => '72.50',
        ], $overrides);
    }

    /** @test */
    public function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);
        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(403);
    }

    /** @test */
    public function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user      = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert   = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(404);
    }

    /** @test */
    public function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(404);
    }

    /** @test */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $otherUser = factory(User::class)->create();
        $concert   = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $response = $this->get("/backstage/concerts/999/edit");

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();
        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'Old title',
            'subtitle'               => 'Old sub title',
            'additional_information' => 'Old addition info',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'Old venue',
            'address'                => 'Old address',
            'suburb'                 => 'Old suburb',
            'state'                  => 'Old state',
            'zip'                    => '0000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'user_id'                => $user->id,
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional info',
            'date'                   => '2018-12-11',
            'time'                   => '8:00pm',
            'venue'                  => 'New venue',
            'address'                => 'New address',
            'suburb'                 => 'New suburb',
            'state'                  => 'New state',
            'zip'                    => '9999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertRedirect("/backstage/concerts");
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals('New additional info', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-11 8:00pm'), $concert->date);
            $this->assertEquals('New venue', $concert->venue);
            $this->assertEquals('New address', $concert->address);
            $this->assertEquals('New suburb', $concert->suburb);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('9999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
        });
    }

    /** @test */
    public function promoters_cannot_edit_other_unpublished_concerts()
    {
        $user      = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert   = factory(Concert::class)->create([
            'user_id'                => $otherUser->id,
            'title'                  => 'Old title',
            'subtitle'               => 'Old subtitle',
            'additional_information' => 'Old additional info',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'Old venue',
            'address'                => 'Old address',
            'suburb'                 => 'Old suburb',
            'state'                  => 'Old state',
            'zip'                    => '0000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'user_id'                => $user->id,
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional info',
            'date'                   => '2018-12-11',
            'time'                   => '8:00pm',
            'venue'                  => 'New venue',
            'address'                => 'New address',
            'suburb'                 => 'New suburb',
            'state'                  => 'New state',
            'zip'                    => '9999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertStatus(404);
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional info', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->address);
            $this->assertEquals('Old suburb', $concert->suburb);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('0000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    /** @test */
    public function promoters_cannot_edit_published_concerts()
    {
        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create([
            'user_id'                => $user->id,
            'title'                  => 'Old title',
            'subtitle'               => 'Old subtitle',
            'additional_information' => 'Old additional info',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'Old venue',
            'address'                => 'Old address',
            'suburb'                 => 'Old suburb',
            'state'                  => 'Old state',
            'zip'                    => '0000',
            'ticket_price'           => 2000,
        ]);

        $this->assertTrue($concert->isPublished());
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'user_id'                => $user->id,
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional info',
            'date'                   => '2018-12-11',
            'time'                   => '8:00pm',
            'venue'                  => 'New venue',
            'address'                => 'New address',
            'suburb'                 => 'New suburb',
            'state'                  => 'New state',
            'zip'                    => '9999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertStatus(403);
        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional info', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->address);
            $this->assertEquals('Old suburb', $concert->suburb);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('0000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    /** @test */
    public function guests_cannot_edit_concerts()
    {
        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'Old title',
            'subtitle'               => 'Old subtitle',
            'additional_information' => 'Old additional info',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'Old venue',
            'address'                => 'Old address',
            'suburb'                 => 'Old suburb',
            'state'                  => 'Old state',
            'zip'                    => '0000',
            'ticket_price'           => 2000,
        ]);

        $this->assertFalse($concert->isPublished());
        $response = $this->patch("/backstage/concerts/{$concert->id}", [
            'user_id'                => $user->id,
            'title'                  => 'New title',
            'subtitle'               => 'New subtitle',
            'additional_information' => 'New additional info',
            'date'                   => '2018-12-11',
            'time'                   => '8:00pm',
            'venue'                  => 'New venue',
            'address'                => 'New address',
            'suburb'                 => 'New suburb',
            'state'                  => 'New state',
            'zip'                    => '9999',
            'ticket_price'           => '72.50',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional info', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->address);
            $this->assertEquals('Old suburb', $concert->suburb);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('0000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    /** @test */
    public function title_is_required()
    {

        $user    = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id'                => $user->id,
            'title'                  => 'Old title',
            'subtitle'               => 'Old subtitle',
            'additional_information' => 'Old additional info',
            'date'                   => Carbon::parse('2017-01-01 5:00pm'),
            'venue'                  => 'Old venue',
            'address'                => 'Old address',
            'suburb'                 => 'Old suburb',
            'state'                  => 'Old state',
            'zip'                    => '0000',
            'ticket_price'           => 2000,
        ]);
        $this->assertFalse($concert->isPublished());

        session()->setPreviousUrl(url("/backstage/concerts/{$concert->id}/edit"));
        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", $this->validParams(['title' => '']));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional info', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-01-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->address);
            $this->assertEquals('Old suburb', $concert->suburb);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('0000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

}
