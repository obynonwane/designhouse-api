<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminaate\Bus\Queable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as Notification;

class VerifyEmail extends Notification
{
    //notifiaable here is the user
    protected function verificationUrl($notifiable)
    {
        //build the url required 
        $appUrl = config('app.client_url', config('app.url'));
        $url = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['user', $notifiable->id]
        );

        return str_replace(url('/api'), $appUrl, $url);
    }
}
