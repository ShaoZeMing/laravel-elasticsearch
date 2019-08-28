<?php

use ShaoZeMing\LaravelElasticsearch\ElasticquentInterface;
use ShaoZeMing\LaravelElasticsearch\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;

class TestModel extends Eloquent implements ElasticquentInterface {

    use ElasticquentTrait;

    protected $table = 'test_table';

    protected $fillable = array('name');
}
