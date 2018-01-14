<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

//Usually, define function doesn't involve anything nullable
//In this case, for example, 'published_at' is omitted, since it's nullable
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

$factory->state(App\Concert::class, 'published', function ($faker) {
    return [
        'published_at' => carbon::parse('-1 week'),
    ];
});

$factory->state(App\Concert::class, 'unpublished', function ($faker) {
    return [
        'published_at' => null,
    ];
});
