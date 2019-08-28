<?php

namespace ShaoZeMing\LaravelElasticsearch;

//use Illuminate\Foundation\Application;
use Laravel\Lumen\Application;

class ElasticquentSupport
{
    use ElasticquentClientTrait;

    public static function isLaravel5()
    {
        return version_compare(Application::VERSION, '5', '>');
    }

}
