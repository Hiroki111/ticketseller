<?php

namespace Tests\Browser;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ViewOrderTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $concert = factory(Concert::class)->create();
        $order   = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
        ]);
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id'   => $order->id,
        ]);

        $this->browse(function (Browser $browser) use ($order) {

            $response = $this->get('/orders/ORDERCONFIRMATION1234');
            $response->assertStatus(200);
            $response->assertViewHas('order', function ($viewOrder) use ($order) {
                return $order->id === $viewOrder->id;
            });
        });
    }
}
