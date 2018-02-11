<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'title'                  => 'The Red chord',
            'subtitle'               => 'with Animostiy and lethargy',
            'date'                   => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price'           => 3250,
            'venue'                  => 'Brisbane Hall',
            'address'                => '8 Clunies Ross Ct',
            'suburb'                 => 'Eight Mile Plains',
            'state'                  => 'QLD',
            'zip'                    => '4113',
            'additional_information' => '',
        ]);

        $response = $this->get('/concerts/' . $concert->id);

        $response->assertSee('The Red chord');
        $response->assertSee('with Animostiy and lethargy');
        $response->assertSee('December 13, 2016');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('Brisbane Hall');
        $response->assertSee('8 Clunies Ross Ct');
        $response->assertSee('Eight Mile Plains');
        $response->assertSee('QLD');
        $response->assertSee('4113');
    }
}
