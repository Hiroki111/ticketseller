<?php

namespace App\Billing;

use App\Billing\PaymentFailedException;

//Why do we use it instead of actually using Stripe?
//One benefit of using a class like this is that
//you don't need to use the internet
//(you can run test offline)
class FakePaymentGateway implements PaymentGateway
{
    private $charges;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken()
    {
        return "valid-token";
    }

    public function charge($amount, $token)
    {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentFailedException;
        }
        $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }
}
