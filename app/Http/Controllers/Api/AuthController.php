<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;


class AuthController extends Controller
{
    use AuthenticatesUsers;

    public function accessToken(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->credentials($request);

        $token = Auth::guard('api')->attempt($credentials);
        if ($token) {
            return $this->sendLoginResponse($request, $token);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function logout(Request $request)
    {
        Auth::guard('api')->logout();
        return response()->json([], 204);
    }

    public function refreshToken(Request $request)
    {
        $token = Auth::guard('api')->refresh();
        return $this->sendLoginResponse($request, $token);
    }

    protected function sendLoginResponse(Request $request, $token)
    {
        return response()->json(['token' => $token]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return response()->json(['message' => Lang::get('auth.failed')], 400);
    }

    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn($this->throttleKey($request));

        $message = Lang::get('auth.throttle', ['seconds' => $seconds]);

        return response()->json(['message' => $message], 403);
    }
}
