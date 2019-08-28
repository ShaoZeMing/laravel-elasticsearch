<?php

use ShaoZeMing\LaravelElasticsearch\ElasticquentInterface;
use ShaoZeMing\LaravelElasticsearch\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;

class CustomTestModel extends Eloquent implements ElasticquentInterface {

    use ElasticquentTrait;

    protected $fillable = array('name');

    function getIndexDocumentData()
    {
        return array('foo' => 'bar');
    }
}
