<?php

namespace Tests;

use App\User;
use Auth;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credential()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email'    => 'blah@gmail.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $res = $this->post('/login', [
            'email'    => 'blah@gmail.com',
            'password' => 'super-secret-password',
        ]);

        $res->assertRedirect('/backstage/concerts');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function logging_in_with_invalid_credential()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create([
            'email'    => 'blah@gmail.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $res = $this->post('/login', [
            'email'    => 'blah@gmail.com',
            'password' => 'wrong-password',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_in_with_an_account_that_does_not_exist()
    {
        $this->withoutExceptionHandling();

        $res = $this->post('/login', [
            'email'    => 'nobody@gmail.com',
            'password' => 'wrong-password',
        ]);

        $res->assertRedirect('/login');
        $res->assertSessionHasErrors('email');
        $this->assertFalse(Auth::check());
    }
}
