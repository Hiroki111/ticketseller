<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00am'),
        ]);

        $date = $concert->formatted_date;

        $this->assertEquals('December 1, 2016', $date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA  = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB  = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $unpublishedConcert = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcert));
    }

    /** @test */
    public function concerts_can_be_published()
    {
        $concert = factory(Concert::class)->create([
            'published_at'    => null,
            'ticket_quantity' => 5,
        ]);
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());

        $concert->publish();

        $this->assertTrue($concert->isPublished());
        $this->assertEquals(5, $concert->ticketsRemaining());
    }

    /** @test */
    public function can_add_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_sold_only_includes_tickets_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(3, $concert->ticketsSold());
    }

    /** @test */
    public function trying_to_reserve_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $reservation = $concert->reserveTickets(11, 'blah@gmail.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('blah@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Order suceeded even though there were not enough tickets remaining.");
    }

    /** @test */
    public function total_tickets_includes_all_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => null]));

        $this->assertEquals(5, $concert->totalTickets());
    }

    /** @test */
    public function calculating_the_percentage_of_tickets_sold()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => null]));

        //$this->assertEquals(0.285714286, $concert->percentSoldOut(), '', 0.00001);
        $this->assertEquals(28.57, $concert->percentSoldOut());
    }

    /** @test */
    public function calculating_the_revenue_in_dollars()
    {
        $concert = factory(Concert::class)->create();
        $orderA  = factory(Order::class)->create(['amount' => 3850]);
        $orderB  = factory(Order::class)->create(['amount' => 9625]);
        $concert->tickets()->saveMany(factory(Ticket::class, 2)->create(['order_id' => $orderA->id]));
        $concert->tickets()->saveMany(factory(Ticket::class, 5)->create(['order_id' => $orderB->id]));

        $this->assertEquals(134.75, $concert->revenueInDollars());
    }

    /** @test */
    public function can_reserve_available_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'blah@gmail.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('blah@gmail.com', $reservation->email());
        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $order   = factory(Order::class)->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'blah@gmail.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Reserving tickets succeeded even though the tickets were already sold.");
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->reserveTickets(2, 'blah@gmail.com');

        try {
            $concert->reserveTickets(2, 'blahblah@gmail.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Reserving tickets succeeded even though the tickets were already reserved.");
    }
}
