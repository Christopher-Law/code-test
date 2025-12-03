<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function getCurrentUser(): User
    {
        $user = Auth::user() ?? User::first();

        if (! $user) {
            abort(404, 'No user found. Please seed the database.');
        }

        return $user;
    }
}
