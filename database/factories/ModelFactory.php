<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

//Usually, defining a model doesn't involve anything nullable in its fields
//In this case, for example, 'published_at' is omitted, since it's nullable
$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'title'                  => 'Example band',
        'subtitle'               => 'with Example Openers',
        'date'                   => Carbon::parse('December 13, 2016 8:00pm'),
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

$factory->define(App\Order::class, function (Faker $faker) {
    return [
        'amount' => 5250,
        'email'  => 'blah@gmail.com',
    ];
});

$factory->define(App\Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
            return factory(App\Concert::class)->create()->id;
        },
    ];
});

$factory->state(App\Ticket::class, 'reserved', function ($faker) {
    return [
        'reserved_at' => carbon::now(),
    ];
});
