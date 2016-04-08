Orchestrate.io PHP Client
======

A very user-friendly PHP client for [Orchestrate.io](https://orchestrate.io) DBaaS.

- [Sync or Async](#quick-start) requests.
- [toArray/toJson](#data-access) methods produces the same output format as Orchestrate's export.
- Orchestrate's [error responses](https://orchestrate.io/docs/apiref#errors) are honored.
- [Template engine friendly](#template-engine), with [JMESPath](#jmespath) support.
- Create [Models](#models) by extending our classes, and easily change child classes.
- [Serialization](#serialization) is supported.
- Adheres to PHP-FIG [PSR-2](http://www.php-fig.org/psr/psr-2/) and [PSR-4](http://www.php-fig.org/psr/psr-4/)

Add helpful features which Orchestrate API doesn't support (yet):
- [Bi-directional relation](#graph-put).
- [Get total item, event or relationship count](#collection-info) of a Collection or entire Application.
- [Load resulting value](#keyvalue-patch-partial-update---operations) of an item after Patching.

Sample integrations:
- Sample code on how to integrate our client in a [Phalcon project](https://github.com/andrefelipe/orchestrate-phalcon).

Requirements:
- PHP must be 5.5 or higher.
- [Guzzle 6](https://github.com/guzzle/guzzle) as HTTP client.
- [JMESPath](https://github.com/jmespath/jmespath.php).


[![Latest Stable Version](https://img.shields.io/packagist/v/andrefelipe/orchestrate-php.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)
[![Total Downloads](https://img.shields.io/packagist/dt/andrefelipe/orchestrate-php.svg)](https://packagist.org/packages/andrefelipe/orchestrate-php)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/andrefelipe/orchestrate-php/master/LICENSE.md)


> This client follows very closely Orchestrate's naming conventions, so you can confidently rely on the [Orchestrate API Reference](https://orchestrate.io/docs/apiref).

> We still are at 0.x version, there is a [lot of ideas](https://github.com/andrefelipe/orchestrate-php/blob/master/TODO.md) to look at.



## Instalation

Use [Composer](http://getcomposer.org).

Install Composer Globally (Linux / Unix / OSX):

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

Run this Composer command to install the latest stable version of the client, in the current folder:

```bash
composer require andrefelipe/orchestrate-php
```

After installing, require Composer's autoloader and you're good to go:

```php
<?php
require 'vendor/autoload.php';
```




## Quick Start

##### Our library is designed to reflect actual Orchestrate's objects (Collection, KeyValue, Event, etc), where each object provides the API, values and response status.

```php
use andrefelipe\Orchestrate\Application;

// provide the parameters, in order: apiKey, host, version
$application = new Application(
    'your-api-key',
    'https://api.aws-eu-west-1.orchestrate.io/',
    'v0'
);

$application = new Application();
// if you don't provide any parameters it will:
// get the API key from an environment variable 'ORCHESTRATE_API_KEY'
// use the default host 'https://api.orchestrate.io'
// and the default API version 'v0'

$collection = $application->collection('collection');
$item = $collection->item('key');
// no API calls where made yet

if ($item->get()) { // API call to get the current key

    // IMPORTANT: The result of all synchronous operations in Objects are boolean

    // let's add some values
    $item->name = 'Lorem Ipsum';
    $item->role = ['member', 'user', 'admin'];

    // put back
    if ($item->put()) {
        
       // if the put operation was successful
       // take the opportunity to post an event too
       $item->event('log')->post(['some' => 'value']);
    }
}
```



## Responses

##### Our objects holds the results as well as the response status.

Example:

```php
$item = $collection->item('key'); // returns a KeyValue object

if ($item->get()) {
    // ok, request successful
    
    // at any time, get the Value out
    print_r($item->getValue());
    // Array
    // (
    //     [title] => My Title
    //     [file_url] => http://myfile.jpg
    // )

    // or an Array of the Orchestrate object
    print_r($item->toArray());
    // Array
    // (
    //     [kind] => item
    //     [path] => Array
    //         (
    //             [collection] => collection
    //             [kind] => item
    //             [key] => key
    //             [ref] => cbb48f9464612f20
    //         )
    //     [value] => Array
    //         (
    //             [title] => My Title
    //             [file_url] => http://myfile.jpg
    //         )
    // )

    // to Json too
    echo $item->toJson(JSON_PRETTY_PRINT);
    // or
    echo json_encode($item, JSON_PRETTY_PRINT);
    // {
    //     "kind": "item",
    //     "path": {
    //         "collection": "collection",
    //         "kind": "item",
    //         "key": "key",
    //         "ref": "cbb48f9464612f20"
    //     },
    //     "value": {
    //         "title": "My Title",
    //         "file_url": "http://myfile.jpg"
    //     }
    // }

    // or by all means, just access each value directly
    echo $item->title;
    echo $item->file_url;

} else {
    // in case of error, like 404, it would return results like these:

    $e = $item->getException();
    // GuzzleHttp\Exception\ClientException

    echo $item->getStatusCode();
    // 404
    // — the HTTP response status code

    echo $item->getReasonPhrase();
    // The requested items could not be found.
    // — the Orchestrate Error Description

    echo $item->getOrchestrateRequestId();
    // ec96acd0-ac7b-11e4-8cf6-22000a0d84a1
    // - Orchestrate request id, X-ORCHESTRATE-REQ-ID header
    
    $response = $item->getResponse());
    // Psr\Http\Message\ResponseInterface

    print_r($item->getBodyArray());
    // Array
    // (
    //     [message] => The requested items could not be found.
    //     [details] => Array
    //         (
    //             [items] => Array
    //                 (
    //                     [0] => Array
    //                         (
    //                             [collection] => collection
    //                             [key] => key
    //                         )
    //                 )
    //         )
    //     [code] => items_not_found
    // )
    // — the full body of the response, in this case, the Orchestrate error
    
}

// All synchronous operations returns boolean regarding the success, but at any time
// you can still check if the last operation was successful or not with:

if ($item->isSuccess()) {
    
} else {
    $e = $item->getException();
    echo $item->getStatusCode();
    echo $item->getReasonPhrase();
}
// the negation is also available: $item->isError()

```



## Async

##### Asynchronous operations are available on KeyValue, Event and Relationship objects. Collection and other objects will follow as soon as a proper pagination feature is implemented.


Every API operation has a sibling "Async" method, for example: get/getAsync, put/putAsync, delete/deleteAsync, etc.
```php
$item = $collection->item('key');

// for KeyValue, these are all the methods available
$promise = $item->getAsync();
$promise = $item->putAsync();
$promise = $item->putIfAsync();
$promise = $item->putIfNoneAsync();
$promise = $item->postAsync();
$promise = $item->patchAsync();
$promise = $item->patchIfAsync();
$promise = $item->patchMergeAsync();
$promise = $item->patchMergeIfAsync();
$promise = $item->deleteAsync();
$promise = $item->deleteIfAsync();
$promise = $item->purgeAsync();
```

The promise returned by these methods are provided by the [Guzzle promises library](https://github.com/guzzle/promises). This means that you can chain then() calls off of the promise. What differs in our implementation is that, as the response is stored within the target object itself, when a promise is fulfilled it returns the actual object, and when it is rejected, it return RejectedPromiseException, a subclass of GuzzleHttp\Promise\RejectionException where you can regain access to the target object.

##### Chaining:
```php
use andrefelipe\Orchestrate\Exception\RejectedPromiseException;

$promise = $item->getAsync();
$promise->then(
    function (KeyValue $item) {
        print_r($item->getValue());
    },
    function (RejectedPromiseException $e) {
        echo $e->getMessage();
        
        // get target
        $item = $e->getTarget();

        // maybe retry?
        $item->get(); 
    }
);
```

##### Resolving a single promise:
```php
use andrefelipe\Orchestrate\Exception\RejectedPromiseException;

// resolve a single promise with unwrap
$promise = $item->getAsync();

try {
    // get target object
    $item = $promise->wait();

} catch (RejectedPromiseException $e) {
    // all async rejects of our library uses this subclass 
    // RejectedPromiseException which adds a method to provide target object, 
    // so you can retry or better handle the exception
    $item = $e->getTarget();

} catch (\Exception $e) {
    // handle exception
    // this exception will only be generated if you customized the async operation
}


// resolve a single promise without unwrap
$promise = $item->getAsync();
$promise->wait(false);
print_r($item->toArray());

// for the even lazier
$promise = $item->getAsync();
print_r($item->toArray());

// Each object can make several API operations, therefore, before reading data or 
// issuing any new request, it will try to settle any outstanding promise.
// So by reading any property like $item->my_prop it will automatically resolve 
// the internal reference to the last promise created.
```



##### Resolve several async promises concurrently:
```php
$promises = [
    'key' => $collection->item('key')->getAsync(),
    'foo' => $collection->item('foo')->getAsync(),
    'bar' => $collection->item('bar')->getAsync(),
    'unexistent' => $collection->item('unexistent')->getAsync(),
];

// At this point you can resolve using the best way for your use case, so please
// take a look at https://github.com/guzzle/promises/blob/master/src/functions.php

// Anyway, we do provide an additional helper function, called 'resolve'
// which waits on all of the provided promises and returns the results,
// in the same order the promises were provided.

use andrefelipe\Orchestrate;

$results = Orchestrate\resolve($promises);

print_r($results);
Array
(
    [key] => KeyValue
    [foo] => KeyValue
    [bar] => KeyValue
    [unexistent] => RejectedPromiseException
)

foreach ($results as $key => $item) {
    if ($item instanceof RejectedPromiseException) {
        continue;
    }
    ...
}
```





## Client / Factory
##### An object factory is provided, which you can choose for one-step operations.

```php
use andrefelipe\Orchestrate\Client;

// provide the parameters, in order: apiKey, host, version
$client = new Client(
    'your-api-key',
    'https://api.aws-eu-west-1.orchestrate.io/',
    'v0'
);

$client = new Client();
// if you don't provide any parameters it will:
// get the API key from an environment variable 'ORCHESTRATE_API_KEY'
// use the default host 'https://api.orchestrate.io'
// and the default API version 'v0'

// check the connection success with Ping (returns boolean)
if ($client->ping()) {
    // OK
}

// use it
$item = $client->get('collection', 'key'); // returns a KeyValue object
$item = $client->put('collection', 'key', ['title' => 'My Title']);
$item = $client->delete('collection', 'key');

// To check the success of an operation use:
if ($item->isSuccess()) {
    // OK, API call sucessful

    // more on using the results and responses below
}

// IMPORTANT: The result of all operations by the Client are 'Objects' (see above).

// Async operations are not yet provided until the next version of our library, 
// which I'll upgrade the Collection classes with proper pagination and async operations.

// The documentation below reflects only the Object usage, for the full list of 
// available methods, please refer to the source code.
```


**Note**, the Http client is automatically instantiated by the `Application` and `Client` objects, and all objects created by them have the Http client set, ready to make API calls. If you are programatically instantiating objects (i.e. new KeyValue()), use the `setHttpClient(GuzzleHttp\ClientInterface $client)` method to have them able to do API class.





## Cache Middleware

You can and should definitly consider a cache for the HTTP requests. That can be achieved with a Guzzle Cache Middleware.

This is a fine [helper library by Kevinrob](https://github.com/Kevinrob/guzzle-cache-middleware) that will get you running quickly.

And here is a basic sample code using Memcached:
```php
// composer require kevinrob/guzzle-cache-middleware
// composer require doctrine/cache

use andrefelipe\Orchestrate;
use andrefelipe\Orchestrate\Application;

use Doctrine\Common\Cache\MemcachedCache;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;

// Create default HandlerStack
$stack = HandlerStack::create();

// Create memcached instance
$memcached = new \Memcached();
$memcached->addServer(
    '127.0.0.1',
    11211
);
$cache = new MemcachedCache();
$cache->setMemcached($memcached);

// Add this middleware to the top with `push`
$stack->push(
    new CacheMiddleware(
        new GreedyCacheStrategy(
            new DoctrineCacheStorage(
                $cache
            )
        , 48 * 60 * 60) // 48 hours
    ), 
    'cache'
);

// OK, the stack is ready, now let's insert into our library

// Use our helper function to get the base config array
$config = Orchestrate\default_http_config($apiKey);

// Add the handler stack
$config['handler'] = $stack;

// Initialize the Guzzle client
$client = new Client($config);

// Add the custom HTTP client to any object
// In this case, let's add to an Application so every instance created with it 
// will inherit the same HTTP client
$application = new Application();
$application->setHttpClient($client);

// Good to go
$collection = $application->collection('name');
$item = $collection->item('key');
if ($item->get()) {
    ...
}
```





## Data Access

All objects implements PHP's magic [get/setter](http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members), [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.iteratoraggregate.php), so you can access the results directly, using either Object or Array syntax.

Example:

```php
// Considering KeyValue with the value of {"title": "My Title"}

$item = $collection->item('key');

if ($item->get()) {

    // Object syntax
    echo $item->title;

    // Array syntax
    echo $item['title'];
    echo $item['another_prop']; // returns null if not set, but never error

    // as intended you can change the Value, then put back to Orchestrate
    $item->file_url = 'http://myfile.jpg';

    if ($item->put())) {
        echo $item->getRef(); // cbb48f9464612f20 (the new ref)
        echo $item->getReasonPhrase();  // OK
        echo $item->getStatusCode();  // 200
    }
}

// it also gets interesting on a collection of items

if ($collection->search('collection', 'title:"The Title*"')) {

    // where you can iterate over the results directly
    foreach ($collection as $item) {
        echo $item->title;
    }

    // also use the Array syntax
    $item = $collection[0];
    // returns null if not set, but never error
}

```





## Template Engine

Easily send your data to your favorite template engine. Also find handy methods to quickly format the data output.

For example:
```php
// Phalcon / Volt - http://phalconphp.com

// gets 50 members, sorted by created_date
$members->search('*', 'created_date:desc', null, 50);

// in a controller you can send the entire object to the view:
$this->view->members = $members;

// then in volt:
<ul class="members-list">
    {%- for member in members %}
        <li><a href="/members/{{ member.getKey() }}/">{{ member.name }}</a></li>
    {%- endfor %}
</ul>

// you may be interested anyway in sending ONLY the data to the view,
// without exposing the KeyValue objects and their API methods (get/put..)

// so send each member's Value only
$this->view->members = $members->getValues();

// or format the data using a JMESPath expression
$this->view->members = $members->extract('results[].{name: value.fullname, country: value.country, slug: path.key}');

// if you don't need the 'path' use extractValues method for a less verbose expression
$this->view->members = $members->extractValues('[].{name: fullname, country: country}');


// Same for single items
$item = $members->item('key');
$item->get();

$this->view->member = $item;
$this->view->member = $item->getValue();
$this->view->member = $item->extractValue('{name: fullname, country: country}');

// then in volt:
<p>Member {{ member['name'] }}, {{ member['country'] }}</p>



// Same approach works for any template engine
```



## JMESPath

```php
// 'extract' method uses the toArray() as data source
$result = $collection->extract('[].{name: value.name, thumb: value.thumbs[0], slug: path.key)}');
$result = $item->extract('{name: value.name, thumb: value.thumbs[0]}');

// 'extractValue' uses getValue() as data source, so you can use less verbose expressions
// with the drawback that you can't access the 'path' data (key/ref/etc...)
$result = $collection->extractValues('[].{name: name, thumb: thumbs[0])}');
$result = $item->extractValue('{name: name, thumb: thumbs[0]}');
```





## Models / ODM

There's one major decision on our library: **KeyValue and Event's values are stored in the object itself**. So when you get a property with $item->myProp you are accessing it directly. — That won't differ much from an Array memory-wise, because we are storing data as dynamic vars. But by extending a KeyValue class and defining our public properties you can actually reduce the memory allocation.

That provides the basis for an ODM. Our objects (KeyValue, Event and Relationship) map directly to the items in your Orchestrate database, so by extending them, you can define your properties, validation, default values, etc.

For example, let's start with a very simple KeyValue, made to reflect a 'Member' of our project:

```php
// Member.php
namespace MyProject\Models;

use andrefelipe\Orchestrate\KeyValue;

class Member extends KeyValue
{
    public $name;

    public $role = 'guest';
    // feel free to set the defaults!
    
    public $birth_date;
    // unset or null vars do not get Put into Orchestrate.
    // Rest assure that setting the public vars here will not be stored
    // in your account, if they are null.
    // To check what is going to be stored there, use the toArray() method
}
```

Getter/setters are fine too! You could use it you favor to add validation, error protection, custom defaults, etc.

```php
// Member.php
namespace MyProject\Models;

use andrefelipe\Orchestrate\KeyValue;

class Member extends KeyValue
{
    protected $country_code;

    protected $created_date;
    // protected and private vars get out of the public scope
    // and therefore do not compose the Value data

    public function __construct()
    {
        $this->mapProperty('created_date');
        $this->mapProperty('country_code');

        // mapProperty will automatically try to match the methods named
        // after your property with camelCase, for example 'getName'.

        // you can also strictly name the get/setters to map to:
        // $this->mapProperty('birth_date', 'getBirth', 'setBirth');
    }

    public function getCreatedDate()
    {
        // custom default example:
        if (!isset($this->created_date)) {
            $this->created_date = date(DATE_W3C);
        }
        return $this->created_date;
    }

    public function setCreatedDate($value)
    {
        $this->created_date = $value;
    }
    
    public function getCountryCode()
    {
        // custom default example:
        if (!isset($this->country_code)) {
            $this->country_code = 'us';
        }
        return $this->country_code;
    }

    public function setCountryCode($value)
    {
        $this->country_code = $value;
        // here you could add a check if the country code is valid
        // could match to a country name and store it too, etc
    }
}
```

Two distinct things happen when using custom getter/setters:

1- Access of the defined property gets mapped to the respective method. For example $item->country_code actually calls getCountryCode and $item->country_code = 'us' actually calls setCountryCode.

2- You strictly notify which additional properties the object should handle, so it knows what to include in the Value that will be sent to Orchestrate.

> Remember, the Value data is composed of all your public properties plus the custom one defined with mapProperty. To check what will be sent to Orchestrate use the toArray method.

The example above is a class that will represent each item in a Collection.

In that case you will also want to create a class to act as your Collection:

```php
// Members.php
namespace MyProject\Models;

use andrefelipe\Orchestrate\Collection;
use GuzzleHttp\ClientInterface;

class Members extends Collection
{
    public function __construct(ClientInterface $httpClient)
    {
        // set http client
        $this->setHttpClient($httpClient);

        // set collection name
        $this->setCollection('members');

        // set child classes
        $this->setItemClass('MyProject\Models\Member');

        // could set the Event class too if desired
        // $this->setEventClass('MyProject\Models\MemberActivity');
    }
}

// at this approach, whenever we create items from this collection,
// it will be Member objects

// for example, let's instantiate a Http client programatically
use MyProject\Models\Members;
use andrefelipe\Orchestrate;
use GuzzleHttp\Client as GuzzleClient;

// creates a pre-configured Guzzle Client with the default settings
$httpClient = Orchestrate\default_http_client();

// instatiate the collection
$members = new Members($httpClient);

// and the member
$item = $members->item('john'); // instance of Member class

```

If you are using the Client class, you can change which classes to use with: 
```php

$client->setItemClass($class);
$client->setEventClass($class);

// where $class is a fully qualified name of a class that implements, at minimum:
// andrefelipe\Orchestrate\Contracts\KeyValueInterface for KeyValue
// andrefelipe\Orchestrate\Contracts\EventInterface for Event
```

For a pratical example, please view [this project](https://github.com/andrefelipe/orchestrate-phalcon).





## Serialization

Objects can be serialized and stored in your prefered cache for later re-use. 

It is valuable to cache in JSON format, because any part of your application, in any language, could take advantage of that cache. But if your use case is strictly PHP you can have the best performance. In my ultra simple test, serialization is 3 times faster than JSON decoding and instantiation.

```php
// serialize in PHP's format
$item = $collection->item('john');
if ($item->get()) {
    file_put_contents('your-cache-path', serialize($item));
}

// serialize entire collections
if ($collection->search('*', 'value.created_date:desc', null, 100)) {
    file_put_contents('your-cache-path-collection', serialize($collection));
}

// instantiation
$data = file_get_contents('your-cache-path');
$item = unserialize($data);

$data = file_get_contents('your-cache-path-collection');
$collection = unserialize($data);



// serialize in JSON
$item = $collection->item('john');
if ($item->get()) {
    file_put_contents('your-cache-path', json_encode($item));
}

// serialize entire collections
if ($collection->search('*', 'value.created_date:desc', null, 100)) {
    file_put_contents('your-cache-path-collection', json_encode($collection));
}

// instantiation
// you can't recreate your custom classes with JSON
// but you can work in a similar way
$data = file_get_contents('your-cache-path');
$item = (new KeyValue())->init(json_decode($data, true));

$data = file_get_contents('your-cache-path-collection');
$collection = (new Collection())->init(json_decode($data, true));


```













# Orchestrate API


### Application Ping:

```php
if ($application->ping()) {
    // success
}
```



### Collection Info:

```php
// get total item count of the Collection
echo $collection->getTotalItems();

// get total event count of the Collection
echo $collection->getTotalEvents();
echo $collection->getTotalEvents('type'); // specific event type

// get total relationship count of the Collection
echo $collection->getTotalRelationships();
echo $collection->getTotalRelationships('type'); // specific relation type

// same goes for the entire Application
echo $application->getTotalItems();
echo $application->getTotalEvents();
echo $application->getTotalEvents('type');
echo $application->getTotalRelationships();
echo $application->getTotalRelationships('type');
```


### Collection Delete:

```php
// To prevent accidental deletions, provide the current collection name as
// the parameter. The collection will only be deleted if both names match.
if ($collection->delete('collection')) {
    // success
}

// Warning this will permanently erase all data
// within the collection and cannot be reversed!
```


### Key/Value Get

```php
$item = $collection->item('key');

// you can check operation success direcly
if ($item->get()) {
    // returns boolean of operation success
}

// Example of getting the object info
$item->getKey(); // string
$item->getRef(); // string
$item->getValue(); // Array of the Value
$item->toArray(); // Array of the Orchestrate object (with path and value)
$item->toJson(); // Json of the Orchestrate object
$item->getBodyArray(); // Array of the unfiltered HTTP response body

```


### Key/Value Put (create/update by key)

```php
$item = $collection->item('key'); // no API calls yet
$item->put(['title' => 'New Title']); // puts a new value
// or manage the value then put later
$item->title = 'New Title';
$item->put();

// at any time check what will actually be put to Orchestrate with
print_r($item->getValue());
// or, for the full Orchestrate object
print_r($item->toArray());
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$item = $collection->item('key');
$item->putIf('20c14e8965d6cbb0', ['title' => 'New Title']);
$item->putIf(true, ['title' => 'New Title']); // uses the current object Ref, if set

// you can set the value direcly to the object too
$item->get();
$item->title = 'New Title'; // check what will be stored with toArray() or getValue()
$item->putIf(); // will be saved only if the current ref is the same
```


**Conditional Put If-None-Match**:

Stores the value for the key if no key/value already exists.

```php
$item = $collection->item('key');
$item->putIfNone(['title' => 'New Title']);

// you can set the value direcly to the object too
$item->title = 'New Title'; // check what will be stored with toArray() or getValue()
$item->putIfNone(); // will be saved only if the current ref is the same
```


### Key/Value Patch (partial update - Operations)

Please refer to the [API Reference](https://orchestrate.io/docs/apiref#keyvalue-patch) for all details about the operations.

```php
// use the Patch operation builder
use andrefelipe\Orchestrate\Query\PatchBuilder;

$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

$item = $collection->item('key');
$item->patch($patch);

// Warning: when patching, the object Value (retrievable with $item->getValue())
// WILL NOT be updated! Orchestrate does not (yet) return the Value body in
// Patch operations, and mocking on our side will be very inconsistent
// and an extra GET would have to issued anyway.

// As a solution, you can fetch the resulting Value, using the
// second parameter 'reload' as:
$item->patch($patch, true);
// it will reload the data if the patch was successful
```

**Conditional Patch (Operations) If-Match**:

Updates the value for the key if the value for this header matches the current ref value.

```php
$patch = (new PatchBuilder())
    ->add('birth_place.city', 'New York')
    ->copy('full_name', 'name');

$item = $collection->item('key');
$item->patchIf('20c14e8965d6cbb0', $patch);
$item->patchIf(true, $patch); // uses the current object Ref
$item->patchIf(true, $patch, true); // with the reload as mentioned above


```


### Key/Value Patch (partial update - Merge)

```php
$item = $collection->item('key');
$item->title = 'New Title';
$item->patchMerge(); // merges the current Value
$item->patchMerge(['title' => 'New Title']); // or merge with new value
// also has a 'reload' parameter as mentioned above
```


**Conditional Patch (Merge) If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$item = $collection->item('key');
$item->patchMergeIf('20c14e8965d6cbb0', ['title' => 'New Title']);
$item->patchMergeIf(true, ['title' => 'New Title']); // uses the current object Ref
// also has a 'reload' parameter as mentioned above
```



### Key/Value Post (create & generate key)

```php
$item = $collection->item();
$item->post(['title' => 'New Title']); // posts a new value
// or manage the object values then post later
$item->title = 'New Title';
$item->post();
```


### Key/Value Delete

```php
$item = $collection->item('key');
$item->delete();
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
$item = $collection->item('key');
// first get the item, or set a ref:
// $item->get();
// or $item->setRef('20c14e8965d6cbb0');
$item->deleteIf(true); // delete the current ref
$item->deleteIf('20c14e8965d6cbb0'); // delete a specific ref
```


**Purge**:

The KV object and all of its ref history will be permanently deleted. This operation cannot be undone.

```php
$item = $collection->item('key');
$item->purge();
```



### Key/Value List:

```php
// range parameter is optional, but when needed
// use the Key Range operation builder
use andrefelipe\Orchestrate\Query\KeyRangeBuilder;

$range = (new KeyRangeBuilder())
    ->from('blue')
    ->to('zinc');

$range = (new KeyRangeBuilder())
    ->from('blue', false) // key 'blue' is excluded, if exists
    ->to('zinc', false); // key 'zinc' is excluded, if exists

// you can also use the between method
$range->between('blue', 'zinc');

// in either method, keys can also be a KeyValue object
$range->from($item)->to($anotherItem);


$collection->get(100, $range);

// Please note, the max limit currently imposed by Orchestrate is 100


// now get the results
$collection->getResults();

// or go ahead and iterate over them directly!
foreach ($collection as $item) {
    
    echo $item->title;
    // items are KeyValue objects
}

// pagination
$collection->getNextUrl(); // string
$collection->getPrevUrl(); // string
count($collection); // count of the current set of results
$collection->getTotalCount(); // count of the total results
$collection->nextPage(); // loads next set of results
$collection->prevPage(); // loads previous set of results
```



### Refs Get:

Returns the specified version of a value.

```php
$item = $collection->item('key');
$item->get('20c14e8965d6cbb0');
```

### Refs List:

Get the specified version of a value.

```php
$refs = $collection->refs('key');
// or $refs = $item->refs();
$refs->get(100);

// now get array of the results
$refs->getResults();

// or go ahead and iterate over the results directly!
foreach ($refs as $item) {
    
    echo $item->title;
}

// pagination
$refs->getNextUrl(); // string
$refs->getPrevUrl(); // string
count($refs); // count of the current set of results
$refs->getTotalCount(); // count of the total results
$refs->nextPage(); // loads next set of results
$refs->prevPage(); // loads previous set of results
```





### Root Search:

```php
$application->search('@path.kind:* AND title:"The Title*"');


// one way of getting array of the search results
$itemList = $results->getResults();

// serialize as json
echo json_encode($application, JSON_PRETTY_PRINT);

// or go ahead and iterate over the results directly
foreach ($application as $item) {
    
    echo $item->title;

    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query
}

// aggregates
$application->getAggregates(); // array of the Aggregate results, if any 

// pagination
$application->getNextUrl(); // string
$application->getPrevUrl(); // string
count($application); // count of the current set of results
$application->getTotalCount(); // count of the total results
$application->nextPage(); // loads next set of results
$application->prevPage(); // loads previous set of results
```

All Search parameters are supported, and it includes [Geo](https://orchestrate.io/docs/apiref#geo-queries) and [Aggregates](https://orchestrate.io/docs/apiref#aggregates) queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search).
```php
// public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)

// aggregates example
$application->search(
    'value.created_date:[2014-01-01 TO 2014-12-31]',
    null,
    'value.created_date:time_series:month'
);
```

Mixing any object type is supported too:
```php
$application->search('@path.kind:(item event relationship) AND title:"The Title*"');
// results will be either KeyValue, Event or Relation objects
```



### Search:

```php
$collection->search('title:"The Title*"');


// one way of getting array of the search results
$itemList = $results->getResults();

// or go ahead and iterate over the results directly
foreach ($collection as $item) {
    
    echo $item->title;

    $item->getScore(); // search score
    $item->getDistance(); // populated if it was a Geo query
}

// aggregates
$collection->getAggregates(); // array of the Aggregate results, if any 

// pagination
$collection->getNextUrl(); // string
$collection->getPrevUrl(); // string
count($collection); // count of the current set of results
$collection->getTotalCount(); // count of the total results
$collection->nextPage(); // loads next set of results
$collection->prevPage(); // loads previous set of results
```

All Search parameters are supported, and it includes [Geo](https://orchestrate.io/docs/apiref#geo-queries) and [Aggregates](https://orchestrate.io/docs/apiref#aggregates) queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search).
```php
// public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)

// aggregates example
$collection->search(
    'value.created_date:[2014-01-01 TO 2014-12-31]',
    null,
    'value.created_date:time_series:month'
);
```

Mixing any object type is supported too:
```php
$collection->search('@path.kind:(item event) AND title:"The Title*"');
// results will be either KeyValue, Event or Relation objects
```



### Event Get

```php
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->get();
```

### Event Put (update)

```php
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->put(['title' => 'New Title']); // puts a new value
// or manage the value then put later
$event->title = 'New Title';
$event->put();
```


**Conditional Put If-Match**:

Stores the value for the key only if the value of the ref matches the current stored ref.

```php
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->putIf('20c14e8965d6cbb0', ['title' => 'New Title']);
$event->putIf(true, ['title' => 'New Title']); // uses the current object Ref, in case you have it, or loaded before with ->get()
```


### Event Post (create)

```php
$item = $collection->item('key');
$event = $item->event('type');

if ($event->post(['title' => 'New Title'])) {
    // success

    // you can also chain the methods if you like:
    // $item->event('type')->post(['title' => 'New Title'])
}

$event->post(); // posts the current Value
$event->post(['title' => 'New Title']); // posts a new value
$event->post(['title' => 'New Title'], 1400684480732); // optional timestamp
$event->post(['title' => 'New Title'], true); // use stored timestamp
```


### Event Delete

Warning: Orchestrate do not support full history of each event, so the delete operation have the purge=true parameter.

```php
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->delete();
```


**Conditional Delete If-Match**:

The If-Match header specifies that the delete operation will succeed if and only if the ref value matches current stored ref.

```php
$item = $collection->item('key');
$event = $item->event('type', 1400684480732, 1);
$event->deleteIf(true); // delete the current ref
$event->deleteIf('20c14e8965d6cbb0'); // delete a specific ref
```


### Event List:

```php
// range parameter is optional, but when needed
// use the Time Range operation builder
use andrefelipe\Orchestrate\Query\TimeRangeBuilder;

$range = (new TimeRangeBuilder())
    ->from('1994-11-06T01:49:37-07:00')
    ->to('2015-11-06T01:49:37-07:00');
// use any supported timestamp format as described here:
// https://orchestrate.io/docs/apiref#events-timestamps

$range = (new TimeRangeBuilder())
    ->from(784111777000, false) // excludes events that match the start time, if exists
    ->to(784111777221, false); // excludes events that match the end time, if exists

// if you don't need millisecond precision, confortably use the 'Date' methods
$range = (new TimeRangeBuilder())
    ->fromDate('yesterday')
    ->toDate('now'));
// any of the following formats are accepted:
// (1) A valid format that strtotime understands;
// (2) A integer, that will be considered as seconds since epoch;
// (3) A DateTime object; 

// you can also use the between method
$range->betweenDate('2015-03-09', '2015-03-11');

// keys can also be an Event object
$range->from($event)->to($anotherEvent);


// from Collection
$events = $collection->events('key', 'type');
// from KeyValue
$events = $item->events('type'); // note the plural 'events'

$events->get(10, $range);


// now get array of the results
$events->getResults();

// or go ahead and iterate over the results directly!
foreach ($events as $event) {
    
    echo $event->title;
    // items are Event objects
}

// pagination
$events->getNextUrl(); // string
$events->getPrevUrl(); // string
count($events); // count of the current set of results
$events->getTotalCount(); // count of the total results
$events->nextPage(); // loads next set of results
$events->prevPage(); // loads previous set of results
```




### Event Search:

```php
$events = $collection->events();
$events->search('title:"The Title*"');

// optionaly add key and event type with:
$events = $collection->events('key', 'type');
// or
$events->setKey('key');
$events->setType('type');
// then do the api call
$events->search('title:"The Title*"');

// Also, create Events objects from KeyValues too:
$events = $item->events('type');
// where it will already have the Key set


// As you may guess, the query parameter is prefixed with:
// @path.kind:event AND @path.key:your_key AND @path.type:your_type
// where key and type is only added if not empty


// now go ahead and iterate over the results directly
foreach ($events as $event) {
    
    echo $event->title;

    $event->getScore(); // search score
    $event->getDistance(); // populated if it was a Geo query
}

// aggregates
$events->getAggregates(); // array of the Aggregate results, if any 

// pagination
$events->getNextUrl(); // string
$events->getPrevUrl(); // string
count($events); // count of the current set of results
$events->getTotalCount(); // count of the total results
$events->nextPage(); // loads next set of results
$events->prevPage(); // loads previous set of results
```

Like the [Collection Search](#search), all search parameters are supported, and it includes [Geo](https://orchestrate.io/docs/apiref#geo-queries) and [Aggregates](https://orchestrate.io/docs/apiref#aggregates) queries. Please refer to the [API Reference](https://orchestrate.io/docs/apiref#search).
```php
// public function search($query, $sort=null, $aggregate=null, $limit=10, $offset=0)

// aggregates example
$events->search(
    'value.created_date:[2014-01-01 TO 2014-12-31]',
    null,
    'value.created_date:time_series:month'
);
```







### Graph List:

Returns relation's collection, key, ref, and values. The "kind" parameter(s) indicate which relations to walk and the depth to walk.

```php
$item = $collection->item('key');
$relations = $item->relationships('kind');
$relations->get(100);

// Kind param can be array too, to indicate the depth to walk
$relations = $item->relationships(['kind', 'another-kind']);


// get array of the results (KeyValue objects)
$relations->getResults();

// or go ahead and iterate over the results directly
foreach ($relations as $item) {
    
    echo $item->title;
    // items are KeyValue objects
}

// pagination
$relations->getNextUrl(); // string
$relations->getPrevUrl(); // string
count($relations); // count of the current set of results
$relations->getTotalCount(); // count of the total results, if available
$relations->nextPage(); // loads next set of results
$relations->prevPage(); // loads previous set of results

```


### Graph Put

```php
$item = $collection->item('key');
$anotherItem = $collection->item('another-key');

$relation = $item->relationship('kind', $anotherItem);

if ($relation->put()) {
    // success

} else {
    // check http status message
    echo $relation->getReasonPhrase();
}

// TIP: Relations are one way operations. We relate an item to another,
// but that other item doesn't automatically gets related back to the calling item.

// To make life easier we implemented that two-way operation, so both source
// and destination items relates to each other.

if ($relation->putBoth()) {
    // success, now both items are related to each other

    // Note that 2 API calls are made in this operation,
    // and the operation success is given only if both are
    // successful.
}

```


### Graph Put with Properties

```php
$values = ['title' => 'My Title'];

$item = $collection->item('key');
$anotherItem = $collection->item('another-key');

$relation = $item->relationship('kind', $anotherItem);

if ($relation->put($values)) {
    // success
}

if ($relation->putBoth($values)) {
    // success
}

```


### Graph Delete

Deletes a relationship between two objects. Relations don't have a history, so the operation have the purge=true parameter.

```php
$item = $collection->item('key');
$anotherItem = $collection->item('another-key');

$relation = $item->relationship('kind', $anotherItem);

if ($relation->delete()) {
    // success
}

// Same two-way operation can be made here too:
if ($relation->deleteBoth()) {
    // success, now both items are not related to each other anymore
}

```




## Docs

Please refer to the source code for now, while a proper documentation is made.


## Useful Notes

Here are some useful notes to consider when using the Orchestrate service:
- When adding a field for a date, suffix it with '_date' or other [supported prefixes](https://orchestrate.io/docs/apiref#sorting-by-date);
- Avoid using dashes in properties names, not required, but makes easier to be accessed directly in JS or PHP, without need to wrap in item['my-prop'] or item->{'my-prop'};
- If applicable, remember you can use a composite key like `{deviceID}_{sensorID}_{timestamp}` for your KeyValue keys, as the List query supports key filtering. More info here: https://orchestrate.io/blog/2014/05/22/the-primary-key/ and API here: https://orchestrate.io/docs/apiref#keyvalue-list;



## Postscript

This client is actively maintained. I am using in production at [www.anzuclub.com](https://www.anzuclub.com) and developing the next version of [typo/graphic posters](https://www.typographicposters.com) plus a few smaller apps.

