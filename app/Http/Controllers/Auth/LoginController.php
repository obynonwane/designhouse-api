<?php

namespace App\Http\Controllers\Auth;



use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{


    use AuthenticatesUsers;

    public function  attemptLogin(Request $request)
    {
        //attempt to issue a token to the user based on the login credentials
        $token = $this->guard()->attempt(
            $this->credentials($request)
        );

        if (!$token) {
            return false;
        }

        //get the authenticated user
        $user = $this->guard()->user();

        // check if the user has verified email
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return false;
        }

        //set the users token 
        $this->guard()->setToken($token);
        return true;
    }

    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        //get the token from the authenticated guard
        $token = (string) $this->guard()->getToken();

        //extract expiry date
        $expiration = $this->guard()->getPayload()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration,

        ]);
    }

    protected function sendFailedLoginResponse()
    {
        $user = $this->guard()->user();
        // check if the user has verified email
        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json(["errors" => [
                "verification" => "You need to verify your email account"
            ]]);
        }

        throw ValidationException::withMessages([
            $this->username() => "Invalid Credentials"
        ]);
    }


    public function logout()
    {
        $this->guard()->logout();

        return response()->json([
            "message" => "Logout Successfully"
        ]);
    }
}
