<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiException;
use App\Exceptions\UserBlockedException;
use App\Exceptions\UserStatusInvalidException;
use App\Http\Requests\ActivateUserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\UserToOrganization;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class AuthController extends Controller
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * login user
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws ApiException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $password = $request->get('password');
        $email = $request->get('email');

        $findUser = User::query()
            ->where('email', $email)
            ->where('status', User::STATUS_ACTIVE)
            ->first();

        if (! $findUser || ! Hash::check($password, $findUser->password)) {
            throw new ApiException(401, ['message' => 'Invalid password.'], 401);
        }

        if (! $token = auth()->login($findUser)) {
            throw new ApiException(500, ['message' => 'Something went wrong.'], 500);
        }
        return response()->json($this->respondWithToken($token));
    }

    /**
     * add new user
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->user->store($request->only(['name','email','password']));
        return response()->json(['item' => $user]);
    }

    /**
     * activate new user with email code
     * @param ActivateUserRequest $request
     * @return JsonResponse
     */
    public function activate(ActivateUserRequest $request): JsonResponse
    {
        $user = $this->user->activate($request->only(['email','code']));
        return response()->json(['item' => $user]);
    }

    /**
     * refresh token
     * @return JsonResponse
     * @throws ApiException
     */
    public function refresh(): JsonResponse
    {
        try {
            $findUser = JWTAuth::parseToken()->authenticate();

            if ($findUser->status !== User::STATUS_ACTIVE) {
                throw new UserStatusInvalidException();
            }

            $newToken = auth()->refresh(false, true);
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                // Token is Expired(need login)
                throw new ApiException(401, ['message' => 'Token expired.'], 401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException) {
                // Token is Invalid
                throw new ApiException(401, ['message' => 'Token is invalid.'], 401);
            } else if ($e instanceof UserStatusInvalidException) {
                //User id blocked
                throw new ApiException(403, ['message' => 'User is not active.'], 403);
            } else {
                throw new ApiException(401, ['message' => 'Something went wrong.'], 401);
            }
        }

        return response()->json($this->respondWithToken($newToken));
    }

    /**
     * Log out
     * @return JsonResponse
     * @throws ApiException
     */
    public function logout(): JsonResponse
    {
        try {
            auth()->logout();
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                // Token is Invalid
                throw new ApiException(401, ['message' => 'Token is invalid.'], 401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                // Token is Expired(need login)
                throw new ApiException(401, ['message' => 'Token expired.'], 401);
            } else {
                throw new ApiException(401, ['message' => 'Something went wrong.'], 401);
            }
        }
        return response()->json(['result' => true]);
    }

    /**
     * @param $token
     * @return array
     */
    private function respondWithToken($token): array
    {
        $parseToken = JWTAuth::decode(new Token($token));

        $createTime = Carbon::parse($parseToken->get('nbf'));
        $expireTime = Carbon::parse($parseToken->get('exp'));
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'create_time' => $createTime,
            'expires_in' => $expireTime,
            'user_id' => auth()->user()->id
        ];
    }
}
