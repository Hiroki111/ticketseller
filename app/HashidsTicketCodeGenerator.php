<?php

namespace App;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private $hashids;

    public function __construct($salt)
    {
        $this->hashids = new \Hashids\Hashids($salt, 6, 'ABCDEFGHJKLMNPQRSTUVWXYZ');
    }

    public function generateFor($ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}
