<?php
namespace andrefelipe\Orchestrate;

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
 *
 * @param object $object
 * @return array
 */
function object_to_array($object)
{
    $result = [];

    foreach (get_object_vars($object) as $key => $value) {

        if ($value === null) {
            continue;
        }
        if (is_object($value)) {
            if (method_exists($value, 'toArray')) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
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