<?php

namespace App\Providers;

use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\Contracts\AvailabilityServiceInterface;
use App\Services\Contracts\BookingServiceInterface;
use App\Services\Contracts\PricingServiceInterface;
use App\Services\PricingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PricingServiceInterface::class, PricingService::class);
        $this->app->bind(BookingServiceInterface::class, BookingService::class);
        $this->app->bind(AvailabilityServiceInterface::class, AvailabilityService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('bookings', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
