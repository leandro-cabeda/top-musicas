<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;


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
    public function boot()
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function ($job) {
            return $job->limiter()
                ->by('api_' . request()->ip())
                ->every(60) // Limite de 60 requisições por minuto
                ->max(100); // Máximo de 100 requisições por minuto
        });
    }
}
