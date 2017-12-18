<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class BlockIoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('BlockIo', function ($app) {
            $apiKey = env('BLOCK_IO_API_KEY');
            $pin = env('BLOCK_IO_PIN');

            return new \BlockIo($apiKey, $pin);
        });
    }
}