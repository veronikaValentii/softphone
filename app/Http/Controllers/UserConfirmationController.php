<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendConfirmationRequest;
use App\Models\UserConfirmation;
use Illuminate\Http\JsonResponse;

class UserConfirmationController extends Controller
{
    private UserConfirmation $userConfirmation;

    public function __construct(UserConfirmation $userConfirmation)
    {
        $this->userConfirmation = $userConfirmation;
    }

    public function resend(SendConfirmationRequest $request): JsonResponse
    {
        $result = $this->userConfirmation->send($request->all());
        return response()->json(['result' => $result]);
    }
}
