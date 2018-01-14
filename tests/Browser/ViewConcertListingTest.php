<?php

namespace Tests\Browser;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewConcertListingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
    {
        //Arrange
        //Create a model
        $concert = Concert::create([
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
            'published_at'           => Carbon::parse('-1 week'),
        ]);

        $this->browse(function (Browser $browser) use ($concert) {
            //Act
            //View the concert listing
            $browser->visit('/concerts/' . $concert->id);

            //Assert
            //See the concert details
            $browser->assertSee('The Red chord');
            $browser->assertSee('with Animostiy and lethargy');
            $browser->assertSee('December 13, 2016');
            $browser->assertSee('8:00pm');
            $browser->assertSee('32.50');
            $browser->assertSee('Brisbane Hall');
            $browser->assertSee('8 Clunies Ross Ct');
            $browser->assertSee('Eight Mile Plains');
            $browser->assertSee('QLD');
            $browser->assertSee('4113');
        });
    }
}
