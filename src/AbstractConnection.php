<?php
namespace andrefelipe\Orchestrate;

use andrefelipe\Orchestrate\Contracts\ConnectionInterface;
use andrefelipe\Orchestrate\Exception\MissingPropertyException;
use andrefelipe\Orchestrate\Exception\RejectedPromiseException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides client capabilities to an object. It connects to a HTTP client,
 * makes requests, and stores the response and exception, if any.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    /**
     * @var ClientInterface
     */
    private $_httpClient;

    /**
     * @var PromiseInterface
     */
    private $_promise = null;

    /**
     * @var ResponseInterface
     */
    private $_response = null;

    /**
     * @var array
     */
    private $_bodyArray = [];

    /**
     * @var string
     */
    private $_reasonPhrase = '';

    /**
     * @var \Exception
     */
    private $_exception = null;

    public function getHttpClient()
    {
        if (!$this->_httpClient) {
            $this->_httpClient = default_http_client();
        }
        return $this->_httpClient;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->_httpClient = $httpClient;
        return $this;
    }

    protected function settlePromise()
    {
        if ($this->_promise) {
            $this->_promise->wait(false);
        }
    }

    protected function clearResponse()
    {
        // wait for any other async requests to finish
        $this->settlePromise();

        // reset local vars
        $this->_response = null;
        $this->_bodyArray = [];
        $this->_reasonPhrase = '';
        $this->_exception = null;
    }

    public function getResponse()
    {
        $this->settlePromise();

        return $this->_response;
    }

    public function getStatusCode()
    {
        $this->settlePromise();

        return $this->_response ? $this->_response->getStatusCode() : 0;
    }

    public function isSuccess()
    {
        return !$this->isError();
    }

    public function isError()
    {
        $code = $this->getStatusCode();
        return !$code || ($code >= 400 && $code <= 599);
    }

    public function getReasonPhrase()
    {
        $this->settlePromise();

        return $this->_reasonPhrase;
    }

    public function getException()
    {
        $this->settlePromise();

        return $this->_exception;
    }

    protected function setException(\Exception $e)
    {
        $this->_exception = $e;
        $this->_reasonPhrase = $e->getMessage();
    }

    public function getOrchestrateRequestId()
    {
        $this->settlePromise();

        if ($this->_response) {
            $value = $this->_response->getHeader('X-ORCHESTRATE-REQ-ID');
            return isset($value[0]) ? $value[0] : '';
        }
        return '';
    }

    public function getBodyArray()
    {
        $this->settlePromise();

        return $this->_bodyArray;
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
     * @return self
     */
    protected function request($method, $uri = '', array $options = [])
    {
        $this->requestAsync(
            $method,
            function () use ($uri) {
                return $uri;
            },
            function () use ($options) {
                return $options;
            }
        );
        $this->settlePromise();
        return $this;
    } // TODO remove this method later, should not be used when full async is ready

    /**
     * Request asynchronously using the current HTTP client, preparing the
     * success and exception callbacks to transfer results in.
     *
     * @param string   $method          HTTP method
     * @param callable $uriCallable     Must return array of uri parts
     * @param callable $optionsCallable Must return array of request options
     * @param callable $onFulfilled     Option callback to chain on fulfillment
     * @param callable $onRejected      Option callback to chain on rejection
     *
     * @return PromiseInterface
     */
    protected function requestAsync(
                 $method,
        callable $uriCallable = null,
        callable $optionsCallable = null,
        callable $onFulfilled = null,
        callable $onRejected = null
    ) {
        // clear previous responses and settle any async operation
        $this->clearResponse();

        // define request options
        $uri = null;
        $options = [];
        try {
            // uri
            if ($uriCallable) {
                $uri = (array) $uriCallable($this);

                // sanitize uri parts, RFC 3986
                $uri = implode('/', array_map('rawurlencode', $uri));
            }

            // options
            if ($optionsCallable) {
                $options = (array) $optionsCallable($this);

                // safely build query string
                if (isset($options['query']) && is_array($options['query'])) {
                    $options['query'] = http_build_query(
                        $options['query'],
                        null,
                        '&',
                        PHP_QUERY_RFC3986
                    );
                }
            }
        } catch (MissingPropertyException $e) {
            $this->setException($e);
            return new RejectedPromiseException($this->getReasonPhrase(), $this);
        }

        // enforce http exceptions
        $options['http_errors'] = true;

        // request
        $promise = $this->getHttpClient()->requestAsync($method, $uri, $options);
        $self = $this;

        $this->_promise = $promise->then(
            static function (ResponseInterface $response) use ($self) {

                // clear out
                $self->_promise = null;

                // set response
                $self->transferResponseData($response);

                // return the item itself
                return $self;
            },
            static function (RequestException $e) use ($self) {

                // clear out
                $self->_promise = null;

                // set exception
                $self->setException($e);

                // set response, if there is one
                if ($e->hasResponse()) {
                    $self->transferResponseData($e->getResponse());

                    // honor the Orchestrate error messages
                    if (!empty($self->_bodyArray['message'])) {
                        $self->_reasonPhrase = $self->_bodyArray['message'];
                    }
                }

                // return exception, with possibility to get the item back
                return new RejectedPromise(
                    new RejectedPromiseException($self->getReasonPhrase(), $self)
                );
            }
        );

        // chain
        if ($onFulfilled || $onRejected) {
            $this->_promise = $this->_promise->then($onFulfilled, $onRejected);
        }

        return $this->_promise;
    }

    protected function transferResponseData(ResponseInterface $response)
    {
        // set response
        $this->_response = $response;

        // set HTTP Reason-Phrase
        $this->_reasonPhrase = $response->getReasonPhrase();

        // set body
        try {
            $json = $response->getBody();
            $this->_bodyArray = \GuzzleHttp\json_decode($json, true);
        } catch (\InvalidArgumentException $e) {            
            $this->setException($e);
        }
    }
}
