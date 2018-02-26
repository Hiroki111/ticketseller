<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    private function validParams($overrides = [])
    {
        return array_merge([
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'address'                => '123 Fake St.',
            'suburb'                 => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ], $overrides);
    }

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

    /** @test */
    public function adding_a_valid_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'address'                => '123 Fake St.',
            'suburb'                 => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertRedirect('/backstage/concerts');

            $this->assertTrue($concert->user->is($user));

            $this->assertFalse($concert->isPublished());

            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
            $this->assertEquals("You must be 19 years of age to attend this concert.", $concert->additional_information);
            $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
            $this->assertEquals('The Mosh Pit', $concert->venue);
            $this->assertEquals('123 Fake St.', $concert->address);
            $this->assertEquals('Laraville', $concert->suburb);
            $this->assertEquals('ON', $concert->state);
            $this->assertEquals('12345', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticket_quantity);
            $this->assertEquals(0, $concert->ticketsRemaining());
        });
    }

    /** @test */
    public function guests_cannot_add_a_new_concert()
    {
        $this->expectException(AuthenticationException::class);
        $this->withoutExceptionHandling();

        $response = $this->post('/backstage/concerts', $this->validParams());
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function title_is_required()
    {

        $user = factory(User::class)->create();
        session()->setPreviousUrl(url('/backstage/concerts/new'));
        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams(['title' => '']));
        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function subtilte_is_optional()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams(['subtitle' => '']));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $this->assertTrue($concert->user->is($user));
            $response->assertRedirect('/backstage/concerts');
            $this->assertNull($concert->subtitle);
        });
    }

    /** @test */
    public function additional_information_is_optional()
    {
        $this->withoutExceptionHandling();

        $user     = factory(User::class)->create();
        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'additional_information' => "",
        ]));
        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertTrue($concert->user->is($user));
            $this->assertNull($concert->additional_information);
        });
    }

    /** @test */
    public function date_must_be_a_valid_date()
    {

        $user = factory(User::class)->create();
        session()->setPreviousUrl(url('/backstage/concerts/new'));
        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => 'not a date',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'address'                => '123 Fake St.',
            'suburb'                 => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function time_must_be_a_valid_date()
    {

        $user = factory(User::class)->create();
        session()->setPreviousUrl(url('/backstage/concerts/new'));
        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => 'not a time',
            'venue'                  => 'The Mosh Pit',
            'address'                => '123 Fake St.',
            'suburb'                 => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '32.50',
            'ticket_quantity'        => '75',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_numeric()
    {

        $user = factory(User::class)->create();
        session()->setPreviousUrl(url('/backstage/concerts/new'));
        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'address'                => '123 Fake St.',
            'suburb'                 => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => 'not a price',
            'ticket_quantity'        => '75',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_at_least_5()
    {

        $user = factory(User::class)->create();
        session()->setPreviousUrl(url('/backstage/concerts/new'));
        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title'                  => 'No Warning',
            'subtitle'               => 'with Cruel Hand and Backtrack',
            'additional_information' => "You must be 19 years of age to attend this concert.",
            'date'                   => '2017-11-18',
            'time'                   => '8:00pm',
            'venue'                  => 'The Mosh Pit',
            'address'                => '123 Fake St.',
            'suburb'                 => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '12345',
            'ticket_price'           => '4.99',
            'ticket_quantity'        => '75',
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }
}
