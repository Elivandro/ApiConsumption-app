<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
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
        Http::macro('api', function (string $baseUrl, ?string $apiKey = null, int $timeout = 60, ?string $authType = null, ?array $extraHeaders = []): PendingRequest {
            $response = Http::baseUrl($baseUrl)
                ->contentType('application/json')
                ->timeout($timeout)
                ->acceptJson();

            if (! is_null($extraHeaders)) {
                $response = $response->withHeaders($extraHeaders);
            }

            return match (mb_strtolower($authType)) {
                'apikey' => $response->withHeaders(['apikey' => $apiKey]),
                'bearer' => $response->withToken($apiKey),
                default => $response,
            };
        });
    }
}
