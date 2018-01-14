<?php

namespace App\Billing;

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
        $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }
}
