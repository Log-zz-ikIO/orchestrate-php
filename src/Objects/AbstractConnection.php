<?php
namespace andrefelipe\Orchestrate\Objects;

use andrefelipe\Orchestrate as Orchestrate;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides the bare basis, a connection to a HTTP client.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var ClientInterface
     */
    private $_httpClient;

    /**
     * @var array
     */
    private $_body = null;

    /**
     * @var PromiseInterface
     */
    private $_promise = null;

    /**
     * @var ResponseInterface
     */
    private $_response = null;

    /**
     * @var string
     */
    private $_status = null;

    public function getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = Orchestrate\default_http_client();
        }
        return $this->_httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    public function getBody()
    {
        $this->wait();

        return $this->_body;
    }

    public function getResponse()
    {
        $this->wait();

        return $this->_response;
    }

    public function getStatus()
    {
        $this->wait();

        return $this->_status;
    }

    public function getStatusCode()
    {
        $this->wait();

        return $this->_response ? $this->_response->getStatusCode() : 0;
    }

    public function getOrchestrateRequestId()
    {
        $this->wait();

        if ($this->_response) {
            $value = $this->_response->getHeader('X-ORCHESTRATE-REQ-ID');
            return empty($value) ? null : $value[0];
        }
        return null;
    }

    public function isSuccess()
    {
        return !$this->isError();
    }

    public function isError()
    {
        $this->wait();

        $code = $this->getStatusCode();
        return !$code || ($code >= 400 && $code <= 599);
    }

    public function reset()
    {
        $this->wait();

        $this->_response = null;
        $this->_body = null;
        $this->_status = null;
    }

    public function wait()
    {
        if ($this->_promise) {
            try {
                $this->_promise->wait();

            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                $this->_response = null;
                $this->_body = null;
                $this->_status = $e->getMessage();
            }

            $this->_promise = null;
        }
    }

    /**
     * Request using the current HTTP client and store the response and
     * decoded json body internally.
     *
     * More information on the options please go to the Guzzle docs.
     *
     * @param string       $method  HTTP method
     * @param string|array $uri     URI
     * @param array        $options Request options to apply.
     *
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function request($method, $uri = null, array $options = [])
    {
        $options[RequestOptions::SYNCHRONOUS] = true;
        $this->requestAsync($method, $uri, $options);
        return $this->getResponse();
    }

    /**
     * Request asynchronously using the current HTTP client, preparing the
     * success and exception callbacks.
     *
     * More information on the options please go to the Guzzle docs.
     *
     * @param string       $method  HTTP method
     * @param string|array $uri     URI
     * @param array        $options Request options to apply.
     *
     * @return PromiseInterface
     */
    protected function requestAsync($method, $uri = null, array $options = [])
    {
        // wait for any other async requests to finish
        $this->wait();

        // safely build query
        if (isset($options['query']) && is_array($options['query'])) {
            $options['query'] = http_build_query($options['query'], null, '&', PHP_QUERY_RFC3986);
        }

        // reset local vars
        $this->_response = null;
        $this->_body = null;
        $this->_status = null;

        // store in var as we use static functions on the callbacks
        $self = $this;

        // sanitize uri
        if (is_array($uri)) {
            $uri = implode('/', array_map('rawurlencode', $uri)); // RFC 3986
        }

        // request
        $this->_promise = $this->getHttpClient()->requestAsync($method, $uri, $options);

        $promise = $this->_promise->then(
            static function (ResponseInterface $response) use ($self) {

                // clear out
                $self->_promise = null;

                // set response
                $self->setResponse($response);

                // the response is sucessfull, but is it a successful error?
                if ($self->isError()) {
                    return new RejectedPromise($self);
                }

                return $self;
            },
            static function (RequestException $e) use ($self) {

                // clear out
                $self->_promise = null;

                $response = $e->getResponse();
                if ($response) {
                    // set response, if there is one
                    $self->setResponse($response);
                } else {
                    // set error message if none
                    $self->_status = $e->getMessage();
                }

                return new RejectedPromise($self);
            }
        );

        return $promise;
    }

    protected function setResponse(ResponseInterface $response)
    {
        // set response
        $this->_response = $response;

        // set body
        $this->_body = json_decode($this->_response->getBody(), true);

        // set status message
        if ($this->isError() && !empty($this->_body['message'])) {
            // honor the Orchestrate error messages
            $this->_status = $this->_body['message'];
        } else {
            // continue with HTTP Reason-Phrase
            $this->_status = $this->_response->getReasonPhrase();
        }
    }
}
