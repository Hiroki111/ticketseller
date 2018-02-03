<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function calculating_the_total_cost()
    {

        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'blah@gmail.com');

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    public function retrieving_the_customers_email()
    {
        $reservation = new Reservation(collect(), 'blah@gmail.com');

        $this->assertEquals('blah@gmail.com', $reservation->email());
    }

    /** @test */
    public function retrieving_the_reservations_tickets()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'blah@gmail.com');

        $this->assertEquals($tickets, $reservation->tickets());
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

        $reservation = new Reservation($tickets, 'blah@gmail.com');

        $reservation->cancel();

        //With the "spy" function, it is necessary to assert that the expected function has been called.
        //With the "mock" function, the followin foreach is not necessary.
        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }

    /** @test */
    public function completing_a_reservation()
    {
        $concert        = factory(Concert::class)->create(['ticket_price' => 1200]);
        $tickets        = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);
        $reservation    = new Reservation($tickets, 'blah@gmail.com');
        $paymentGateway = new FakePaymentGateway;

        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken());

        $this->assertEquals('blah@gmail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(3600, $paymentGateway->totalCharges());
    }
}
