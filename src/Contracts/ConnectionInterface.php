<?php
namespace andrefelipe\Orchestrate\Contracts;

use GuzzleHttp\ClientInterface;

/**
 * Defines the object HTTP connection methods.
 */
interface ConnectionInterface
{
    /**
     * Gets the current object's HTTP client. If not set yet, it will create
     * a pre-configured Guzzle Client with the default settings.
     *
     * @return ClientInterface
     */
    public function getHttpClient();

    /**
     * Sets the HTTP client which the object will use to make API requests.
     *
     * @param ClientInterface $httpClient
     *
     * @return self
     */
    public function setHttpClient(ClientInterface $httpClient);

    /**
     * Gets the body of the response as associative array.
     *
     * @return array|null Body decoded as associative array, or null if unknown.
     */
    public function getBody();

    /**
     * Get the PSR-7 Response object of the last request.
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Gets the status of the last response.
     * If the request was successful the value is the HTTP Reason-Phrase.
     * If not, the value is the Orchestrate Error Description.
     *
     * @return string|null Reason phrase, or null if unknown.
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatus();

    /**
     * Gets the response status code.
     *
     * @return int Status code.
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatusCode();

    /**
     * Gets the X-ORCHESTRATE-REQ-ID header.
     *
     * @return string|null
     */
    public function getOrchestrateRequestId();

    /**
     * Check if last request was successful.
     *
     * A request is considered successful if status code is not 4xx or 5xx.
     *
     * @return boolean
     */
    public function isSuccess();

    /**
     * Check if last request was unsuccessful.
     *
     * A request is considered error if status code is 4xx or 5xx.
     *
     * @return boolean
     */
    public function isError();

    /**
     * Resets current object for reuse.
     */
    public function reset();
}
