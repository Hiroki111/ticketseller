<?php

namespace App\Facades;

use App\TicketCodeGenerator;
use Illuminate\Support\Facades\Facade;

class TicketCode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TicketCodeGenerator::class;
    }

    //https://laravel.com/api/5.5/Illuminate/Support/Facades/Facade.html#method_getMockableClass
    //This is where the mock will be created from, if it has to create a mock of this facade
    //If the interface (TicketCodeGenerator) has had no implementation yet,
    //overwrite it
    //If it has have an implementation, Laravel will automatically call this.
    //https://course.testdrivenlaravel.com/lessons/module-15/assigning-codes-when-claiming-tickets?autoplay=true#89
    protected static function getMockableClass()
    {
        return static::getFacadeAccessor();
    }
}
