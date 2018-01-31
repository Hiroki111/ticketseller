<?php

namespace Tests\Unit;

use App\Reservation;
use Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    /** @test */
    public function calculating_the_total_cost()
    {

        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    public function reserved_tickets_are_released_when_a_reservation_is_canncelled()
    {
        //The followig works too.
        // $tickets = collect([
        //     Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
        //     Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
        //     Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
        // ]);

        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets);

        $reservation->cancel();

        //With the "spy" function, it is necessary to assert that the expected function has been called.
        //With the "mock" function, the followin foreach is not necessary.
        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }
}
