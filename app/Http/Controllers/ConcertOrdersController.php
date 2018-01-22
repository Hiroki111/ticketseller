<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;

class ConcertOrdersController extends Controller
{

    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);
        $this->validate(request(), [
            'email'           => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token'   => ['required '],
        ]);

        try {
            //Find tickets
            $tickets = $concert->findTickets(request('ticket_quantity'));

            //Charge the customer for the tickets
            $this->paymentGateway->charge(
                request('ticket_quantity') * $concert->ticket_price, request('payment_token')
            );

            //Create an order for the tickets
            $order = $concert->createOrder(request('email'), $tickets);

            return response()->json($order->toArray(), 201);

        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
