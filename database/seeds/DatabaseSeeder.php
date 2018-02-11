<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(ConcertTableSeeder::class);
        $this->call(OrderTableSeeder::class);
        $this->call(TicketTableSeeder::class);

        factory(App\Concert::class)->states('published')->create([
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
        ])->addTickets(10);

    }
}
