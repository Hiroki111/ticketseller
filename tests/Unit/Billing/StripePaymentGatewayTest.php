<?php

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{

    private function lastCharge()
    {
        return array_first(\Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => config('services.stripe.secret')]
        )['data']);
    }

    private function validToken()
    {
        return \Stripe\Token::create([
            "card" => [
                "number"    => "4242424242424242",
                "exp_month" => 1,
                "exp_year"  => date('Y') + 1,
                "cvc"       => "123",
            ],
        ], ['api_key' => config('services.stripe.secret')])->id;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    public function newCharges()
    {
        return \Stripe\Charge::all(
            [
                'limit'         => 1,
                'ending_before' => $this->lastCharge->id,
            ],
            ['api_key' => config('services.stripe.secret')]
        )['data'];

    }

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        //Create a new StripePaymentGateway
        $paymentGateway = $this->getPaymentGateway();

        //Create a new charge for some amount using a valid token
        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
        });

        //Verify that the charge was completed successfully
        $this->assertCount(1, $newCharges);
        $this->assertEquals(5000, $newCharges->sum());
    }

    /** @test */
    public function charges_with_an_invalid_payment_token_fail()
    {
        // try {
        //     $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
        //     $paymentGateway->charge(2500, 'invalid-payment-token');
        // } catch (PaymentFailedException $e) {
        //     $this->assertCount(0, $this->newCharges());
        //     return;
        // }

        // $this->fail("Charging with an invalid payment token did not throw a PaymentFailedException.");

        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
        $result         = $paymentGateway->charge(2500, 'invalid-payment-token');

        $this->assertFalse($result);
    }
}
