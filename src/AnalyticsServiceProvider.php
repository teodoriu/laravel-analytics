<?php

namespace ErlanCarreira\Analytics;

use Illuminate\Support\ServiceProvider;
use ErlanCarreira\Analytics\Exceptions\InvalidConfiguration;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
       
        $this->publishes(
            [
                __DIR__.'/../config/analytics.php' => config_path('analytics.php'),
            ]
        );
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/analytics.php', 'analytics');

        $this->app->bind(
            Analytics::class,
            function () {
                $analyticsConfig = config('analytics');

                $this->guardAgainstInvalidConfiguration($analyticsConfig);

                $path = storage_path('app/analytics/service-account-credentials.json');
        
                putenv("GOOGLE_APPLICATION_CREDENTIALS=$path");

                $client = app(AnalyticsClient::class);

                return new Analytics($client, $analyticsConfig['property_id']);
            }
        );

        

        $this->app->alias(Analytics::class, 'laravel-analyticsV1');

        
    }

    protected function guardAgainstInvalidConfiguration(array $analyticsConfig = null)
    {
        if (empty($analyticsConfig['property_id'])) {
            throw InvalidConfiguration::propertyIdNotSpecified();
        }
    }
}
