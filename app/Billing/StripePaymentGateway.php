<?php

namespace App\Billing;

use App\Billing\PaymentFailedException;
use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        try {
            Charge::create([
                "amount"   => $amount,
                "currency" => "aud",
                "source"   => $token,
            ], ['api_key' => $this->apiKey]);
        } catch (\Stripe\Error\InvalidRequest $e) {
            throw new PaymentFailedException;
        }
    }
}
