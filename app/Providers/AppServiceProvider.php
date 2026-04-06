<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bind Service Contracts
        $this->app->bind(
            \App\Services\Contracts\PricingServiceInterface::class,
            \App\Services\PricingService::class
        );
        $this->app->bind(
            \App\Services\Contracts\BookingServiceInterface::class,
            \App\Services\BookingService::class
        );
        $this->app->bind(
            \App\Services\Contracts\AvailabilityServiceInterface::class,
            \App\Services\AvailabilityService::class
        );

        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('bookings', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
