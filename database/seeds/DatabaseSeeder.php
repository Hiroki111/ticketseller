<?php

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
        $this->call(ConcertTableSeeder::class);
        $this->call(OrderTableSeeder::class);
        $this->call(TicketTableSeeder::class);
    }
}
