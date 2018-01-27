<?php

namespace Tests;

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function creating_an_order_from_tickets_email_and_amount()
    {
        $concert = factory(Concert::class)->create()->addTickets(5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order = Order::forTickets($concert->findTickets(3), 'blah@gmail.com', 3600);

        $this->assertEquals('blah@gmail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    public function converting_to_an_array()
    {
        $concert = factory(Concert::class)->create([
            'ticket_price' => 1200,
        ])->addTickets(5);
        $order = $concert->orderTickets('blah@gmail.com', 5);

        $result = $order->toArray();

        $this->assertEquals($order->toArray(), [
            'email'           => 'blah@gmail.com',
            'ticket_quantity' => 5,
            'amount'          => 6000,
        ]);
    }

    /** @test */
    public function tickets_are_released_when_an_order_is_cancelled()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);
        $order   = $concert->orderTickets('blah@gmail.com', 3);
        $this->assertEquals(7, $concert->ticketsRemaining()); //Arrange part can involve assertions

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
