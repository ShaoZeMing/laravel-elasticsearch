<?php

use ShaoZeMing\LaravelElasticsearch\ElasticquentInterface;
use ShaoZeMing\LaravelElasticsearch\ElasticquentTrait;
use Illuminate\Database\Eloquent\Model as Eloquent;

class TestModelWithCustomTypeName extends Eloquent implements ElasticquentInterface {

    use ElasticquentTrait;

    protected $table = 'test_table';

    protected $fillable = array('name');

    public function getTypeName()
    {
        return 'test_type_name';
    }
}
