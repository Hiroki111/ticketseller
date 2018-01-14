<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class FakePaymentGatewayTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;
    }

}
