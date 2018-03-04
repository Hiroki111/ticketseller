<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConcertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('concerts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('additional_information')->nullable();
            $table->datetime('date');
            $table->integer('ticket_price');
            $table->string('venue');
            $table->string('address');
            $table->string('suburb');
            $table->string('state');
            $table->string('zip');
            $table->integer('ticket_quantity');
            $table->string('poster_image_path')->nullable();
            $table->datetime('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('concerts');
    }
}
