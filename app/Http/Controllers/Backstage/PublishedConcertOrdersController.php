<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PublishedConcertOrdersController extends Controller
{
    public function index($id)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($id);

        return view('backstage.published-concert-orders.index', [
            'concert' => $concert,
            'orders'  => $concert->orders()->latest()->take(10)->get(),
        ]);
    }
}
