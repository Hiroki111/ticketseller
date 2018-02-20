<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;

class LoginController extends Controller
{

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        if (!Auth::attempt(request(['email', 'password']))) {
            return redirect('/login')->withErrors([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        return redirect('/backstage/concerts/new');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }}
