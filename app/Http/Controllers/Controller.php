<?php

namespace App\Http\Controllers;

use App\Models\User;

abstract class Controller
{
    protected function getCurrentUser(): User
    {
        $user = auth()->user() ?? User::first();

        if (! $user) {
            abort(404, 'No user found. Please seed the database.');
        }

        return $user;
    }
}
