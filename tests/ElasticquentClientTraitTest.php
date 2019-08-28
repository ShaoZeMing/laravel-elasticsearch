<?php

class ElasticquentClientTraitTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->model = new TestModel;
    }

    public function testClient()
    {
        $this->assertInstanceOf('ElasticSearch\Client', $this->model->getElasticSearchClient());
    }
}
