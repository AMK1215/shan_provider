<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\AdminResource;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    use HttpResponses;

    public function login(LoginRequest $request)
    {
        $credentials = [
            'user_name' => $request->user_name,
            'password' => $request->password,
        ];

        if (! Auth::attempt($credentials)) {
            return $this->error(null, 'The credentials do not match our records.', 401);
        }

        $user = Auth::user();

        if ($user->is_changed_password == 0) {
            return $this->error(['user_id' => $user->id], 'Password change required.', 403);
        }

        if ($user->status == 0) {
            return $this->error(null, 'Your account is not activated!', 403);
        }

        // Create token if using Sanctum
        $token = $user->createToken('admin-token')->plainTextToken;

        return $this->success([
            'user' => new AdminResource($user),
            'token' => $token,
        ], 'Login successful.');
    }
}
