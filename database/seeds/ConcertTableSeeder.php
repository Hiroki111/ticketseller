<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ConcertTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('concerts')->insert([
            'title'                  => "test title",
            'subtitle'               => "test subtitle",
            'date'                   => Carbon::parse('-2weeks'),
            'ticket_price'           => 2000,
            'venue'                  => "test venue",
            'address'                => "test address",
            'suburb'                 => "test suburb",
            'state'                  => "test state",
            'zip'                    => "test zip",
            'additional_information' => "test additional_information",
        ]);
    }
}
