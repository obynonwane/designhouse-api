<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use App\Repositories\Contracts\IUser;



class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */

    protected $users;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(IUser $user)
    {

        $this->users = $user;
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verify(Request $request, User $user)
    {

        //check if the url is a a valid signed url 
        if (!URL::hasValidSignature($request)) {
            return response()->json(["errors" => [
                "message" => "Invalid Verification Link"
            ]], 422);
        }

        //check if the user has already verified account
        if ($user->hasVerifiedEmail()) {
            return response()->json(["errors" => [
                "message" => "Email Address Already Verified"
            ]], 422);
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return response()->json(['message' => 'Email succesffuly verified'], 200);
    }

    public function resend(Request $request)
    {
        $this->validate($request, [
            'email' => ['email', 'required']
        ]);


        $user = $this->users->findWhereFirst('email', $request->email);
        if (!$user) {
            return response()->json(["error" => [
                "message" => "No User could be found with this email address"
            ]], 422);
        }

        //check if the user has already verified account
        if ($user->hasVerifiedEmail()) {
            return response()->json(["errors" => [
                "message" => "Email Aaddress Already Verified"
            ]], 422);
        }

        //initiate sending verication link
        $user->sendEmailVerificationNotification();

        //responsee once sent
        return response()->json(['status' => 'Verification Link resent']);
    }
}
