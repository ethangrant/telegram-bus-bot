<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\TransportApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TransportApiServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register Transport Api Service
     * @return App/Providers/TransportApi/TransportApi
     */
    public function register()
    {
        $this->app->singleton(TransportApi::class, function () {
            $config = config('transportapi');

            return new TransportApi($config);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [TransportApi::class];
    }
}
