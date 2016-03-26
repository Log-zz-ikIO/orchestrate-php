<?php
namespace andrefelipe\Orchestrate\Contracts;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

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
     * Resets current object for reuse.
     */
    public function reset();

    /**
     * Get the PSR-7 Response object of the last request.
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Gets the status code of the last response.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getStatusCode();

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
     * Gets the response reason phrase associated with the status code.
     *
     * If the request was successful the value is the HTTP Reason-Phrase.
     * If not, the value is the Orchestrate Error Description.
     *
     * @return string Reason phrase, or empty string if unknown.
     * @link https://orchestrate.io/docs/apiref#errors
     */
    public function getReasonPhrase();

    /**
     * Gets the last exception, if any.
     *
     * @return \Exception|null
     */
    public function getException();

    /**
     * Gets the X-ORCHESTRATE-REQ-ID header of the last response.
     *
     * @return string
     */
    public function getOrchestrateRequestId();

    /**
     * Gets the body of the response as associative array.
     *
     * @return array Body decoded as associative array.
     */
    public function getBodyArray();

}
