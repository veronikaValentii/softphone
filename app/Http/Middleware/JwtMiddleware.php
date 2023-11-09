<?php


namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Exceptions\UserStatusInvalidException;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Closure;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws ApiException
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken();
            /**
             * @var $user User
             */
            $user = $request->user();
            if (! $user) {
                throw new \Exception();
            }

            if ($user->status !== User::STATUS_ACTIVE) {
                throw new UserStatusInvalidException();
            }

        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                // Token is Invalid
                throw new ApiException(401, ['message' => 'Token is invalid.'], 401);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                // Token is Expired (need refresh)
                throw new ApiException(401, ['message' => 'Token expired.'], 401);
            } else if ($e instanceof UserStatusInvalidException) {
                $user = JWTAuth::parseToken()->authenticate();
                //User id blocked
                throw new ApiException(403, ['message' => 'User is not active.'], 403);
            } else {
                // Incorrect error
                throw new ApiException(401, ['message' => 'Something went wrong.'], 401);
            }
        }

        return $next($request);
    }
}
