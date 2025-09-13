<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function getAllUsersWithAgents()
    {
        return User::with('agent')->get();
    }
}
