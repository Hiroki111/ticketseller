<?php

namespace App\Http\Controllers;

use App\Order;

class OrdersController extends Controller
{
    public function show($confirmationNumber)
    {
        $order = Order::where('confirmation_number', $confirmationNumber)->first();
        return view('orders.show', ['order' => $order]);
    }
}
