<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TwilioServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('Twilio', function ($app) {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');

            return new \Twilio\Rest\Client($sid, $token);
        });
    }
}