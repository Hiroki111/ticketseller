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
