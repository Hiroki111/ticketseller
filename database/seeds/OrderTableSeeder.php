<?php

use Illuminate\Database\Seeder;

class OrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('orders')->insert([
            'amount' => 2000,
            'email'  => "sample@gmail.com",
        ]);
    }
}
