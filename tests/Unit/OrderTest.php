<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $charge = new Charge([
            'amount'         => 3600,
            'card_last_four' => '1234',
        ]);

        //Spies are a type of test doubles that keep track of the calls they received,
        //and allow us to inspect these calls after the fact.
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'blah@gmail.com', $charge);

        $this->assertEquals('blah@gmail.com', $order->email);
        //$this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
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

        $order->tickets()->saveMany([
            factory(Ticket::class)->create(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->create(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        $this->assertEquals($order->toArray(), [
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'blah@gmail.com',
            'amount'              => 6000,
            'tickets'             => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ],
        ]);
    }

}
