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


        $source = realpath(__DIR__ . '/config/elasticsearch.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('elasticsearch.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('elasticsearch');
        }
        $this->mergeConfigFrom($source, 'elasticsearch');

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Support class
        $this->app->singleton('elasticsearch.support', function () {
            return new ElasticquentSupport;
        });

        // Elasticsearch client instance
        $this->app->singleton('elasticsearch.elasticsearch', function ($app) {
            return $app->make('elasticsearch.support')->getElasticSearchClient();
        });
    }
}
