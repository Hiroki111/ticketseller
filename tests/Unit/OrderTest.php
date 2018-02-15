<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $tickets = factory(Ticket::class, 3)->create();
        $charge  = new Charge([
            'amount'         => 3600,
            'card_last_four' => '1234',
        ]);

        $order = Order::forTickets($tickets, 'blah@gmail.com', $charge);

        $this->assertEquals('blah@gmail.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        try {
            Order::findByConfirmationNumber('THIS_DOESNT_EXIST');
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('No matching order was found for the specified confirmation number, but an excception was not thrown.');
    }

    /** @test */
    public function converting_to_an_array()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'blah@gmail.com',
            'amount'              => 6000,
        ]);

        $order->tickets()->saveMany(factory(Ticket::class)->times(5)->create());

        $result = $order->toArray();

        $this->assertEquals($order->toArray(), [
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'blah@gmail.com',
            'ticket_quantity'     => 5,
            'amount'              => 6000,
        ]);
    }

}
