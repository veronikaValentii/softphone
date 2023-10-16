<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActivateUserRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->user->store($request->only(['name','email','password']));
        return response()->json(['item' => $user]);
    }

    public function activate(ActivateUserRequest $request): JsonResponse
    {
        $user = $this->user->activate($request->only(['email','code']));
        return response()->json(['item' => $user]);
    }
}
