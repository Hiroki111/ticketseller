<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Auth::user()->concerts]);
    }

    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store()
    {
        $this->validate(request(), [
            'title'           => ['required'],
            'date'            => ['required', 'date'],
            'time'            => ['required', 'date_format:g:ia'],
            'ticket_price'    => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
        ]);

        $concert = Auth::user()->concerts()->create([
            'title'                  => request('title'),
            'subtitle'               => request('subtitle'),
            'additional_information' => request('additional_information'),
            'date'                   => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time'),
            ])),
            'venue'                  => request('venue'),
            'address'                => request('address'),
            'suburb'                 => request('suburb'),
            'state'                  => request('state'),
            'zip'                    => request('zip'),
            'ticket_price'           => request('ticket_price') * 100,
        ])->addTickets(request('ticket_quantity'));

        $concert->publish();

        return redirect()->route('concerts.show', $concert);
    }
}
