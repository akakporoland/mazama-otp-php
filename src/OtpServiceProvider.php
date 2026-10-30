<?php

namespace Mazama\Otp;

use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/mazama-otp.php', 'mazama-otp');

        $this->app->singleton(Client::class, fn ($app) => new Client(
            (string) config('mazama-otp.url'),
            (string) config('mazama-otp.token'),
        ));
    }

    public function boot(): void
    {
        $this->publishes([__DIR__ . '/../config/mazama-otp.php' => config_path('mazama-otp.php')], 'mazama-otp-config');
    }
}
