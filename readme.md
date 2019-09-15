# Elasticsearch   for laravel5.*  or  lumen

---
[![](https://travis-ci.org/ShaoZeMing/laravel-elasticsearch.svg?branch=master)](https://travis-ci.org/ShaoZeMing/laravel-elasticsearch) 
[![](https://img.shields.io/packagist/v/ShaoZeMing/laravel-elasticsearch.svg)](https://packagist.org/packages/shaozeming/laravel-elasticsearch) 
[![](https://img.shields.io/packagist/dt/ShaoZeMing/laravel-elasticsearch.svg)](https://packagist.org/packages/shaozeming/laravel-elasticsearch)

## Installing

```shell
$ composer require shaozeming/laravel-elasticsearch -v
```

## Overview

快速使用

- 模型中引用Trait

```php
use ShaoZeMing\LaravelElasticsearch\ElasticquentTrait;

class Book extends Eloquent
{
    use ElasticquentTrait;
}
```

- 添加索引
```php
    $books = Book::where('id', '<', 200)->get();
    $books->addToIndex();
```

- 直接搜索

```php
    $books = Book::search('Moby Dick');
    echo $books->totalHits();
```

同时，您仍然可以使用所有Eloquent集合功能：

```php
    $books = $books->filter(function ($book) {
        return $book->hasISBN();
    });
```


### 工作原理

搜索时候，将从Es数据库中索引数据，并返回Eloquent对象数据



## Setup

开始使用ShaoZeMing\LaravelElasticsearch之前，请确保已安装[Elasticsearch](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/_installation.html).


### Laravel

```php
// config/app.php

    'providers' => [
        //...
        ShaoZeMing\LaravelElasticsearch\ElasticquentServiceProvider::class,    //This is default in laravel 5.5
    ],
```

And publish the config file: 

```shell
$ php artisan vendor:publish --provider=ShaoZeMing\\LaravelElasticsearch\ElasticquentServiceProvider
```

if you want to use facade mode, you can register a facade name what you want to use, for example `LaravelElasticsearch`: 

```php
// config/app.php

    'aliases' => [
        'LaravelElasticsearch' => ShaoZeMing\LaravelElasticsearch\ElasticquentServiceProvider::class,   
    ],
```

### lumen

- 在 bootstrap/app.php 中 82 行左右：
```
$app->register( ShaoZeMing\LaravelElasticsearch\ElasticquentServiceProvider::class);
```
将 `vendor/shaozeming/laravel-elasticsearch/config/elasticsearch.php.php` 拷贝到项目根目录`/config`目录下，并将文件名改成`elasticsearch.php`。



### Configuration


```php
<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Custom Elasticsearch Client Configuration
    |--------------------------------------------------------------------------
    |
    | This array will be passed to the Elasticsearch client.
    | See configuration options here:
    |
    | http://www.elasticsearch.org/guide/en/elasticsearch/client/php-api/current/_configuration.html
    */

    'config' => [
        'hosts'     => ['localhost:9200'],   //es服务器:端口
        'retries'   => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elastiquent will use for all
    | Elastiquent models.
    */

    'default_index' => 'my_custom_index_name',

);

```






Eloquent模型有一些额外的方法，可以使用Elasticsearch更轻松地索引模型的数据。
虽然您可以通过Elasticsearch API构建索引和映射，但您也可以使用一些辅助方法直接从模型构建索引和类型。


如果您想要一种简单的方法来创建索引，ShaoZeMing\LaravelElasticsearch模型具有以下方法：

```php

    Book::createIndex($shards = null, $replicas = null);

```

若需要自定义分析器，您可以在模型中设置indexSettings属性并从那里定义分析器：

```php
    /**
     * The elasticsearch settings.
     *
     * @var array
     */
    protected $indexSettings = [
        'analysis' => [
            'char_filter' => [
                'replace' => [
                    'type' => 'mapping',
                    'mappings' => [
                        '&=> and '
                    ],
                ],
            ],
            'filter' => [
                'word_delimiter' => [
                    'type' => 'word_delimiter',
                    'split_on_numerics' => false,
                    'split_on_case_change' => true,
                    'generate_word_parts' => true,
                    'generate_number_parts' => true,
                    'catenate_all' => true,
                    'preserve_original' => true,
                    'catenate_numbers' => true,
                ]
            ],
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'char_filter' => [
                        'html_strip',
                        'replace',
                    ],
                    'tokenizer' => 'whitespace',
                    'filter' => [
                        'lowercase',
                        'word_delimiter',
                    ],
                ],
            ],
        ],
    ];

```

关于映射，您可以在模型中设置mappingProperties属性，并使用一些映射函数：

```php
protected $mappingProperties = array(
   'title' => array(
        'type' => 'string',
        'analyzer' => 'standard'
    )
);
```

根据映射属性设置模型的类型映射：

```php
    Book::putMapping($ignoreConflicts = true);
```

删除映射:

```php
    Book::deleteMapping();
```

重建映射:

```php
    Book::rebuildMapping();
```

获取类型映射并检查它是否存在。

```php
    Book::mappingExists();
    Book::getMapping();
```

### 设置项目索引名称

配置文件 `default_index` key设置:

```php
return array(

   // Other configuration keys ...
   
   /*
    |--------------------------------------------------------------------------
    | Default Index Name
    |--------------------------------------------------------------------------
    |
    | This is the index name that Elastiquent will use for all
    | Elastiquent models.
    */
    
   'default_index' => 'my_custom_index_name',
);
```


## Indexing Documents

索引整个模型数据 `addAllToIndex`:

```php
    Book::addAllToIndex();
```

索引多条:

```php
    $books = Book::where('id', '<', 200)->get();
    $books->addToIndex();
```

索引单条:

```php
    $book = Book::find($id);
    $book->addToIndex();
```

您还可以重新索引整个模型：
```php
    Book::reindex();
```

## Searching

三种搜索方式

### 简单搜索

第一种方法是搜索所有字段的简单术语搜索。

```php
    $books = Book::search('Moby Dick');
```

### 基于查询的搜索

第二种是基于查询的搜索，以满足更复杂的搜索需求：

```php
    public static function searchByQuery($query = null, $aggregations = null, $sourceFields = null, $limit = null, $offset = null, $sort = null)
```

**Example:**

```php
    $books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));
```
这是可用参数列表：

- query - 您的ElasticSearch查询
- aggregations - 您希望返回的聚合。有关详细信息，请参阅聚合。
- sourceFields - 返回的限制仅设置为选定的字段
- limit - 要返回的记录数
- offset - 设置记录偏移量（用于分页结果）
- sort - 您的排序查询

### 原始查询

最终方法是将发送到Elasticsearch的原始查询。在Elasticsearch中搜索记录时，此方法将为您提供最大的灵活性，搜索语法请查看ES官方文档

```php
    $books = Book::complexSearch(array(
        'body' => array(
            'query' => array(
                'match' => array(
                    'title' => 'Moby Dick'
                )
            )
        )
    ));
```

这条查询相当于:
```php
    $books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));
```

### 搜索结果集合

当您在ShaoZeMing\LaravelElasticsearch模型上搜索时，您将获得具有一些特殊功能的搜索集合。

您可以获得总点击次数：
```php
    $books->totalHits();
```

访问分片数组：
```php
    $books->shards();
```

获得最高分：
```php
    $books->maxScore();
```

访问超时布尔属性：

```php
    $books->timedOut();
```

并访问take属性：

```php
    $books->took();
```

并访问搜索聚合 - 有关详细信息，请参阅聚合： - [See Aggregations for details](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations.html):

```php
    $books->getAggregations();
```

### Search Collection Documents

使用isDocument函数检查并查看模型是否为文档：
```php
    $book->isDocument();
```

检查Elasticsearch分配给此文档的文档分数
```php
    $book->documentScore();
```

### 拆分搜索集合

与Illuminate \ Support \ Collection类似，chunk方法将ShaoZeMing\LaravelElasticsearch集合分解为给定大小的多个较小的集合：

```php
    $all_books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));
    $books = $all_books->chunk(10);
```


### 使用ShaoZeMing\LaravelElasticsearch之外的搜索集合

如果您正在处理来自ShaoZeMing\LaravelElasticsearch外部的原始搜索数据，则可以使用ShaoZeMing\LaravelElasticsearch搜索结果集合将该数据转换为集合。

```php
$client = new \Elasticsearch\Client();

$params = array(
    'index' => 'default',
    'type'  => 'books'
);

$params['body']['query']['match']['title'] = 'Moby Dick';

$collection = Book::hydrateElasticsearchResult($client->search($params));

```

## 其他设置

### 文档索引主键id

将使用设置为您的Eloquent模型的primaryKey作为Elasticsearch文档的ID。

### 文档数据体结构

默认情况下，将使用整个属性数组作为Elasticsearch文档。
但是，如果要自定义搜索文档的结构，可以设置getIndexDocumentData函数，该函数返回您自己的自定义文档数组。

```php
function getIndexDocumentData()
{
    return array(
        'id'      => $this->id,
        'title'   => $this->title,
        'custom'  => 'variable'
    );
}
```

### 将与自定义集合模型一起使用

如果您在Eloquent模型中使用自定义集合，则只需将ElasticquentCollectionTrait添加到集合中，这样就可以使用addToIndex。

```php
class MyCollection extends \Illuminate\Database\Eloquent\Collection
{
    use ElasticquentCollectionTrait;
}
```

## Roadmap

ShaoZeMing\LaravelElasticsearch currently needs:

* Tests that mock ES API calls.
* Support for routes
