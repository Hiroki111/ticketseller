<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

//Usually, defining a model doesn't involve anything nullable in its fields
//In this case, for example, 'published_at' is omitted, since it's nullable
$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'user_id'                => function () {
            return factory(App\User::class)->create()->id;
        },
        'title'                  => 'Example band',
        'subtitle'               => 'with Example Openers',
        'additional_information' => 'Example additional info',
        'date'                   => Carbon::parse('December 13, 2016 8:00pm'),
        'venue'                  => 'Example Hall',
        'address'                => '8 Example St',
        'suburb'                 => 'Example Plains',
        'state'                  => 'EG',
        'zip'                    => '4000',
        'ticket_price'           => 2000,
        'ticket_quantity'        => 5,
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
        'amount'              => 5250,
        'email'               => 'blah@gmail.com',
        'confirmation_number' => 'ORDERCONFIRMATION1234',
        'card_last_four'      => '1234',
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
