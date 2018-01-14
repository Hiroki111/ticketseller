<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PurchaseTicketTest extends TestCase
{

    use DatabaseMigrations;
    /** @test*/
    public function customer_can_purchase_concert_tickets()
    {
        $paymentGateway = new FakePaymentGateway;
        //$this->app->instance is explanied in...
        //https://laravel.com/docs/5.5/container
        $this->app->instance(PaymentGateway::class, $paymentGateway);

        //Arrange
        //Create a concert
        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250,
        ]);

        //Act
        //Purchase concert tickets
        //$this->json is Laravel's helper for testing API
        //https://laravel.com/docs/5.5/http-tests#testing-json-apis
        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $paymentGateway->getValidTestToken(),
        ]);
        $response->assertStatus(201);

        //Assert
        //Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $paymentGateway->totalCharges());
        //Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }
}
