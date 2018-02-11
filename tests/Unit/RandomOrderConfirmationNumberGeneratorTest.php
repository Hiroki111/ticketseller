<?php

namespace Tests\Unit;

use App\RandomOrderConfirmationNumberGenerator;
use Tests\TestCase;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    //Must be 24 characters long
    //Can only contain uppercase and numbers
    //Cannot contain ambiguous characters
    //All order confirmation numbers must be unique

    //ABCDEFGHJKLMNPQRSTUVWXYZ
    //23456789

    /** @test */
    public function must_be_24_characters_long()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertEquals(24, strlen($confirmationNumber));
    }

    /** @test */
    public function can_only_contain_uppercase_letters_and_numbers()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertRegExp('/^[A-Z0-9]+$/', $confirmationNumber);
    }

    /** @test */
    public function cannot_contain_ambiguous_characters()
    {
        $generator = new RandomOrderConfirmationNumberGenerator;

        $confirmationNumber = $generator->generate();

        $this->assertFalse(strpos($confirmationNumber, '1'));
        $this->assertFalse(strpos($confirmationNumber, 'I'));
        $this->assertFalse(strpos($confirmationNumber, '0'));
        $this->assertFalse(strpos($confirmationNumber, 'O'));
    }
}
