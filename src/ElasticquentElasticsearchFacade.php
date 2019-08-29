<?php

namespace ShaoZeMing\LaravelElasticsearch;

use Illuminate\Support\Facades\Facade;

/**
 * @see \ShaoZeMing\LaravelElasticsearch\ElasticquentServiceProvider
 */
class ElasticquentElasticsearchFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'elasticsearch.elasticsearch';
    }
}
