<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurchaseTicketTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        //$this->app->instance is explanied in...
        //https://laravel.com/docs/5.5/container
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
        Mail::fake();

    }

    private function orderTickets($concert, $params)
    {
        $savedRequest         = $this->app['request'];
        $response             = $this->json('POST', "/concerts/{$concert->id}/orders", $params);
        $this->app['request'] = $savedRequest;
        return $response;
    }

    private function assertValidationError($field, $response)
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    /** @test*/
    public function customer_can_purchase_tickets_to_a_pubished_concert()
    {
        $this->withoutExceptionHandling();

        //https://laravel.com/docs/5.5/mocking#mocking-facades
        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

        // $concert = factory(Concert::class)->states('published')->create([
        //     'ticket_price' => 3250,
        // ])->addTickets(3);

        $concert = \ConcertFactory::createPublished([
            'ticket_price'    => 3250,
            'ticket_quantity' => 3,
        ]);

        //$this->json is Laravel's helper for testing API
        //https://laravel.com/docs/5.5/http-tests#testing-json-apis
        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'confirmation_number' => 'ORDERCONFIRMATION1234',
                'email'               => 'john@example.com',
                'amount'              => 9750,
                'tickets'             => [
                    ['code' => 'TICKETCODE1'],
                    ['code' => 'TICKETCODE2'],
                    ['code' => 'TICKETCODE3'],
                ],
            ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $order = $concert->ordersFor('john@example.com')->first();
        $this->assertEquals(3, $order->ticketQuantity());

        Mail::assertSent(OrderConfirmationEmail::class, function ($mail) use ($order) {
            return $mail->hasTo('john@example.com')
            && $mail->order->id === $order->id;
        });
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create();
        $concert->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test*/
    public function an_order_is_not_created_if_payment_fails()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250,
        ])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
    {
        //$this->withoutExceptionHandling();
        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 1200,
        ])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {

            $response = $this->orderTickets($concert, [
                'email'           => 'perB@example.com',
                'ticket_quantity' => 1,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]);

            $response->assertStatus(422);
            $this->assertFalse($concert->hasOrderFor('perB@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $response = $this->orderTickets($concert, [
            'email'           => 'perA@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('perA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('perA@example.com')->first()->ticketQuantity());

        //Find tickets for person A
        //  Find tickets for person B
        //  Attempt to charge person B
        //  Create an order for person B
        //Attempt to charge person A
        //Create an order for person A
    }

    /** @test*/
    public function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email', $response);
    }

    /** @test*/
    public function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email'           => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email', $response);
    }

    /** @test*/
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email'         => 'blah@exampl.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test*/
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email'           => 'blah@exampl.com',
            'ticket_quantity' => 0,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity', $response);
    }

    /** @test*/
    public function payment_token_is_required()
    {
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTickets($concert, [
            'email'           => 'blah@exampl.com',
            'ticket_quantity' => 1,
        ]);

        $this->assertValidationError('payment_token', $response);
    }
}
