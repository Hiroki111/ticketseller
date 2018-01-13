<?php

namespace App\Http\Controllers;

use App\Concert;

class ConcertsController extends Controller
{
    public function show($id)
    {
        $concert = Concert::whereNotNull('published_at')->find($id);
        return view('concerts.show', [
            'concert' => $concert,
        ]);
    }
}
