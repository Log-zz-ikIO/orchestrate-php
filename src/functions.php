<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Exception\RejectedPromiseException;
use GuzzleHttp\Client as GuzzleClient;

const DEFAULT_HOST = 'https://api.orchestrate.io';
const DEFAULT_VERSION = 'v0';

/**
 * Creates a pre-configured Guzzle Client with the default settings.
 *
 * @param string $apiKey  Orchestrate API key. Defaults to getenv('ORCHESTRATE_API_KEY').
 * @param string $host    Orchestrate API host. Defaults to 'https://api.orchestrate.io'
 * @param string $version Orchestrate API version. Defaults to 'v0'
 *
 * @return \GuzzleHttp\Client
 */
function default_http_client($apiKey = null, $host = null, $version = null)
{
    $config = default_http_config($apiKey, $host, $version);
    return new GuzzleClient($config);
}

/**
 * Form default configuration settings for Guzzle Client.
 *
 * @param string $apiKey  Orchestrate API key. Defaults to getenv('ORCHESTRATE_API_KEY').
 * @param string $host    Orchestrate API host. Defaults to 'https://api.orchestrate.io'
 * @param string $version Orchestrate API version. Defaults to 'v0'
 *
 * @return array
 */
function default_http_config($apiKey = null, $host = null, $version = null)
{
    $base_uri = $host ? trim($host, '/') : DEFAULT_HOST;
    $base_uri .= '/'.($version ? trim($version, '/') : DEFAULT_VERSION).'/';

    return [
        'base_uri' => $base_uri,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'auth' => [$apiKey ?: getenv('ORCHESTRATE_API_KEY'), null],
    ];
}

/**
 * Waits on all of the provided promises and returns the results, either the
 * target object on success, or RejectedPromiseException on rejection.
 *
 * The returned array will be in the same order the promises were provided. 
 *
 * In case of rejection, you can gain access to the target object with the
 * getTarget method of RejectedPromiseException, so you can retry or handle
 * the exception.
 *
 * @param mixed $promises Iterable of PromiseInterface objects to wait on.
 *
 * @return array
 */
function resolve($promises)
{
    $results = [];

    foreach ($promises as $key => $promise) {
        try {
            $results[$key] = $promise->wait();

        } catch (\Exception $e) {
            $results[$key] = $e;
        }
    }

    return $results;
}

// function pool($promises, $concurrency=25)
// {
//     $results = [];

//     return (new \GuzzleHttp\Promise\EachPromise(
//         $promises,
//         [
//             'concurrency' => $concurrency,
//             'fulfilled' => function ($value, $idx) use (&$results) {
//                 $results[$idx] = $value;
//             },
//             'rejected' => function ($reason, $idx) use (&$results) {
//                 if ($reason instanceof RejectedPromiseException) {
//                     $results[$idx] = $reason->getTarget();
//                 } else {
//                     $results[$idx] = $reason;
//                 }
//             },
//         ],
//     ))
//     ->promise()
//     ->then(function () use (&$results) {
//         ksort($results);
//         return $results;
//     })
//     ->wait();
// }

// test the usage of each_limit_all
// function each_limit_all(
//     $iterable,
//     $concurrency,
//     callable $onFulfilled = null
// ) {
//     return each_limit(
//         $iterable,
//         $concurrency,
//         $onFulfilled,
//         function ($reason, $idx, PromiseInterface $aggregate) {
//             $aggregate->reject($reason);
//         }
//     );
// }

/**
 * Helper method to merge instance's public properties.
 *
 * @param array|object $source
 * @param object $target
 */
function merge_object($source, $target)
{
    if (is_object($source)) {
        $source = get_object_vars($source);
    } elseif (!is_array($source)) {
        return;
    }
    $index = count($target);

    foreach ($source as $key => $value) {

        if (is_numeric($key)) {
            $key = $index++;
        }
        $key = (string) $key;

        if (isset($target->{$key})
            && is_object($target->{$key})
            && (is_object($value) || is_array($value))
        ) {
            merge_object($value, $target->{$key});
        } else {
            $target->{$key} = $value;
        }
    }
}

/**
 * Gets an object public properties out, into an Array.
 * If any value is an object, and has a toArray method, it will be executed.
 * Will skip null values.
 *
 * @param object $object
 * @return array
 */
function object_to_array($object)
{
    return to_array(get_object_vars($object));
}

/**
 * Return a new array, executing the toArray method of any object found.
 * Will skip null values.
 *
 * @param array $array
 * @return array
 */
function to_array(array $array)
{
    $result = [];

    foreach ($array as $key => $value) {
        if ($value === null) {
            continue;
        }
        if (is_object($value) && method_exists($value, 'toArray')) {
            $result[$key] = $value->toArray();
        } else {
            $result[$key] = $value;
        }
    }

    return $result;
}

/**
 * Helper to get an object public properties. Optionally include the values too.
 *
 * This method uses 'get_object_vars' and the reason is that if you use
 * get_object_vars inside your class, it will get all currently accessible properties,
 * i.e. your private and protected vars. A work around that is to use:
 * $properties = (new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC);
 * but that is a considerable overhead, around 50% slower, at least on PHP 5.5 where I checked.
 *
 * @param object $object
 * @param boolean $includeValues Optionally include the values too. Defaults to false.
 * @return array
 */
function get_public_properties($object, $includeValues = false)
{
    return $includeValues ? get_object_vars($object) : array_keys(get_object_vars($object));
}
