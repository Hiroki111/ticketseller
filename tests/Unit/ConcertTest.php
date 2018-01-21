<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
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
    public function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $order   = $concert->orderTickets('blah@example.com', 3);

        $this->assertEquals('blah@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
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
        $concert = factory(Concert::class)->create()->addTickets(50);
        $concert->orderTickets('blah@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        try {
            $concert->orderTickets('blah@example.com', 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('blah@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Order suceeded even though there were not enough tickets remaining.");
    }

    /** @test */
    public function cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);
        $concert->orderTickets('blah@example.com', 8);

        try {
            $concert->orderTickets('failedOrder@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('failedOrder@example.com'));
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Order suceeded even though there were not enough tickets remaining.");
    }
}
