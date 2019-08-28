<?php

namespace ShaoZeMing\LaravelElasticsearch;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

class ElasticquentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {


        $source = realpath(__DIR__.'/config/elasticquent.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('elasticquent.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('elasticquent');
        }
        $this->mergeConfigFrom($source, 'elasticquent');

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Support class
        $this->app->singleton('elasticquent.support', function () {
            return new ElasticquentSupport;
        });

        // Elasticsearch client instance
        $this->app->singleton('elasticquent.elasticsearch', function ($app) {
            return $app->make('elasticquent.support')->getElasticSearchClient();
        });
    }
}
