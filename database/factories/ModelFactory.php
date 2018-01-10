<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'title'                  => 'Example band',
        'subtitle'               => 'with Example Openers',
        'date'                   => Carbon::parse('+2 weeks'),
        'ticket_price'           => 2000,
        'venue'                  => 'Example Hall',
        'address'                => '8 Example St',
        'suburb'                 => 'Example Plains',
        'state'                  => 'EG',
        'zip'                    => '4000',
        'additional_information' => 'Example additional info',
    ];
});
