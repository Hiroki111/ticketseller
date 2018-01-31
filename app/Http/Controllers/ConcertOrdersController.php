<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Reservation;

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
            $tickets     = $concert->reserveTickets(request('ticket_quantity'));
            $reservation = new Reservation($tickets);

            //Charge the customer for the tickets
            $this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));

            //Create an order for the tickets
            $order = Order::forTickets($tickets, request('email'), $reservation->totalCost());

            return response()->json($order->toArray(), 201);

        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
