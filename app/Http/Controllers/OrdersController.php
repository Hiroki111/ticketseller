<?php

namespace App\Http\Controllers;

use App\Order;

class OrdersController extends Controller
{
    public function show($confirmationNumber)
    {
        $order = Order::findByConfirmationNumber($confirmationNumber);
        return view('orders.show', ['order' => $order]);
    }
}
