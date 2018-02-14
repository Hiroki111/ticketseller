<?php

namespace App\Billing;

use App\Billing\Charge;
use App\Billing\PaymentFailedException;

//Why do we use it instead of actually using Stripe?
//One benefit of using a class like this is that
//you don't need to use the internet
//(you can run test offline)
class FakePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = '4242424242424242';

    private $charges;
    private $tokens;
    private $beforeFirstChargeCalllback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens  = collect();
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        $token                = 'fake-tok_' . str_random(24);
        $this->tokens[$token] = $cardNumber;
        return $token;
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCalllback !== null) {
            $callback                         = $this->beforeFirstChargeCalllback;
            $this->beforeFirstChargeCalllback = null;
            $callback($this);
        }

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount'         => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
        ]);
    }

    public function newChargesDuring($callback)
    {
        $chargesFrom = $this->charges->count();
        $callback($this);
        return $this->charges->slice($chargesFrom)->reverse()->values();
    }

    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCalllback = $callback;
    }
}
