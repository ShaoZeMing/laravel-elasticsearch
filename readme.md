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

快熟使用

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

Plus, you can still use all the Eloquent collection functionality:

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

if you want to use facade mode, you can register a facade name what you want to use, for example `elasticsearch`: 

```php
// config/app.php

    'aliases' => [
        'Translate' => ShaoZeMing\LaravelElasticsearch\ElasticquentServiceProvider::class,   //This is default in laravel 5.5
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
        'hosts'     => ['localhost:9200'],
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


### Indexes and Mapping



Then add the ShaoZeMing\LaravelElasticsearch trait to any Eloquent model that you want to be able to index in Elasticsearch:

```php
use ShaoZeMing\LaravelElasticsearch\ElasticquentTrait;

class Book extends Eloquent
{
    use ElasticquentTrait;
}
```

Now your Eloquent model has some extra methods that make it easier to index your model's data using Elasticsearch.


While you can definitely build your indexes and mapping through the Elasticsearch API, you can also use some helper methods to build indexes and types right from your models.

If you want a simple way to create indexes, ShaoZeMing\LaravelElasticsearch models have a function for that:

    Book::createIndex($shards = null, $replicas = null);

For custom analyzer, you can set an `indexSettings` property in your model and define the analyzers from there:

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

For mapping, you can set a `mappingProperties` property in your model and use some mapping functions from there:

```php
protected $mappingProperties = array(
   'title' => array(
        'type' => 'string',
        'analyzer' => 'standard'
    )
);
```

If you'd like to setup a model's type mapping based on your mapping properties, you can use:

```php
    Book::putMapping($ignoreConflicts = true);
```

To delete a mapping:

```php
    Book::deleteMapping();
```

To rebuild (delete and re-add, useful when you make important changes to your mapping) a mapping:

```php
    Book::rebuildMapping();
```

You can also get the type mapping and check if it exists.

```php
    Book::mappingExists();
    Book::getMapping();
```

### Setting a Custom Index Name

By default, ShaoZeMing\LaravelElasticsearch will look for the `default_index` key within your configuration file(`config/elasticsearch.php`). To set the default value for an index being used, you can edit this file and set the `default_index` key:

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

If you'd like to have a more dynamic index, you can also override the default configuration with a `getIndexName` method inside your Eloquent model:

```php
function getIndexName()
{
    return 'custom_index_name';
}
```

Note: If no index was specified, ShaoZeMing\LaravelElasticsearch will use a hardcoded string with the value of `default`.

### Setting a Custom Type Name

By default, ShaoZeMing\LaravelElasticsearch will use the table name of your models as the type name for indexing. If you'd like to override it, you can with the `getTypeName` function.

```php
function getTypeName()
{
    return 'custom_type_name';
}
```

To check if the type for the ShaoZeMing\LaravelElasticsearch model exists yet, use `typeExists`:

```php
    $typeExists = Book::typeExists();
```

## Indexing Documents

To index all the entries in an Eloquent model, use `addAllToIndex`:

```php
    Book::addAllToIndex();
```

You can also index a collection of models:

```php
    $books = Book::where('id', '<', 200)->get();
    $books->addToIndex();
```

You can index individual entries as well:

```php
    $book = Book::find($id);
    $book->addToIndex();
```

You can also reindex an entire model:

```php
    Book::reindex();
```

## Searching

There are three ways to search in ShaoZeMing\LaravelElasticsearch. All three methods return a search collection.

### Simple term search

The first method is a simple term search that searches all fields.

```php
    $books = Book::search('Moby Dick');
```

### Query Based Search

The second is a query based search for more complex searching needs:

```php
    public static function searchByQuery($query = null, $aggregations = null, $sourceFields = null, $limit = null, $offset = null, $sort = null)
```

**Example:**

```php
    $books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));
```
Here's the list of available parameters:

- `query` - Your ElasticSearch Query
- `aggregations` - The Aggregations you wish to return. [See Aggregations for details](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations.html).
- `sourceFields` - Limits returned set to the selected fields only
- `limit` - Number of records to return
- `offset` - Sets the record offset (use for paging results)
- `sort` - Your sort query

### Raw queries

The final method is a raw query that will be sent to Elasticsearch. This method will provide you with the most flexibility
when searching for records inside Elasticsearch:

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

This is the equivalent to:
```php
    $books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));
```

### Search Collections

When you search on an ShaoZeMing\LaravelElasticsearch model, you get a search collection with some special functions.

You can get total hits:

```php
    $books->totalHits();
```

Access the shards array:

```php
    $books->shards();
```

Access the max score:

```php
    $books->maxScore();
```

Access the timed out boolean property:

```php
    $books->timedOut();
```

And access the took property:

```php
    $books->took();
```

And access search aggregations - [See Aggregations for details](http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/search-aggregations.html):

```php
    $books->getAggregations();
```

### Search Collection Documents

Items in a search result collection will have some extra data that comes from Elasticsearch. You can always check and see if a model is a document or not by using the `isDocument` function:

```php
    $book->isDocument();
```

You can check the document score that Elasticsearch assigned to this document with:

```php
    $book->documentScore();
```

### Chunking results from Elastiquent

Similar to `Illuminate\Support\Collection`, the `chunk` method breaks the ShaoZeMing\LaravelElasticsearch collection into multiple, smaller collections of a given size:

```php
    $all_books = Book::searchByQuery(array('match' => array('title' => 'Moby Dick')));
    $books = $all_books->chunk(10);
```


### Using the Search Collection Outside of ShaoZeMing\LaravelElasticsearch

If you're dealing with raw search data from outside of ShaoZeMing\LaravelElasticsearch, you can use the ShaoZeMing\LaravelElasticsearch search results collection to turn that data into a collection.

```php
$client = new \Elasticsearch\Client();

$params = array(
    'index' => 'default',
    'type'  => 'books'
);

$params['body']['query']['match']['title'] = 'Moby Dick';

$collection = Book::hydrateElasticsearchResult($client->search($params));

```

## More Options

### Document IDs

ShaoZeMing\LaravelElasticsearch will use whatever is set as the `primaryKey` for your Eloquent models as the id for your Elasticsearch documents.

### Document Data

By default, ShaoZeMing\LaravelElasticsearch will use the entire attribute array for your Elasticsearch documents. However, if you want to customize how your search documents are structured, you can set a `getIndexDocumentData` function that returns you own custom document array.

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
Be careful with this, as ShaoZeMing\LaravelElasticsearch reads the document source into the Eloquent model attributes when creating a search result collection, so make sure you are indexing enough data for your the model functionality you want to use.

### Using ShaoZeMing\LaravelElasticsearch With Custom Collections

If you are using a custom collection with your Eloquent models, you just need to add the `ElasticquentCollectionTrait` to your collection so you can use `addToIndex`.

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
