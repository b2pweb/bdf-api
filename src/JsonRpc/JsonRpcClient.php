<?php

namespace Bdf\JsonRpc;

use Bdf\Api\Server\ClientInterface;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * JsonRpcClient
 * 
 * @author Seb
 * @package Bdf\JsonRpc
 */
class JsonRpcClient implements ClientInterface
{
    /**#@+
     * Version Constants
     */
    const VERSION_1 = '1.0';
    const VERSION_2 = '2.0';
    /**#@-*/
    
    /**#@+
     * Feature
     */
    const SINGLE_ELEMENT_ARRAY = 0;
    const SINGLE_ELEMENT_OBJECT = 1;
    /**#@-*/
    
    /**
     * @var GuzzleClientInterface
     */
    protected $httpClient;
    
    /**
     * @var string
     */
    protected $uri;
    
    /**
     * @var int 
     */
    protected $id;
    
    /**
     * @var array 
     */
    protected $serviceMap;

    /**
     * @var bool 
     */
    protected $autoValidate = true;

    /**
     * @var int 
     */
    protected $features = self::SINGLE_ELEMENT_ARRAY;
    
    /**
     * @var string 
     */
    protected $version = self::VERSION_2;
    
    /**
     * @var array
     */
    protected $clientOptions = array();
    
    /**
     * @var array
     */
    private $last = [
        'method'            => null,
        'request'           => null,
        'requestHeaders'    => null,
        'response'          => null,
        'responseHeaders'   => null,
    ];
    
    /**
     * @var bool
     */
    private $isNotification;
    
    
    /**
     * @param string $uri
     * @param array  $options
     */
    public function __construct($uri = '', array $options = array(), $id = 1)
    {
        $this->id = (int)$id;
        
        $this->setUri($uri);
        $this->setOptions($options);
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * @param int $features
     */
    public function setFeatures($features)
    {
        $this->features = $features;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getFeatures()
    {
        return $this->features;
    }
    
    /**
     * @param bool $flag
     */
    public function setAutoValidate($flag)
    {
        $this->autoValidate = (bool) $flag;

        return $this;
    }
    
    /**
     * @return bool
     */
    public function getAutoValidate()
    {
        return $this->autoValidate;
    }
    
    /**
     * Set JSON-RPC version
     *
     * @param  string $version
     * 
     * @return self
     */
    public function setVersion($version)
    {
        $version = (string) $version;

        if (self::VERSION_2 != $version) {
            $this->version = null;
        }

        return $this;
    }

    /**
     * Retrieve JSON-RPC version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * @param array $options
     * 
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->addOption($name, $value);
        }
        
        return $this;
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * 
     * @return $this
     */
    public function addOption($name, $value)
    {
        switch ($name) {
            case 'connect_timeout':
            case 'timeout':
            case 'exceptions':
                $this->clientOptions[$name] = $value;
                break;
            
            case 'trace':
                $this->clientOptions['debug'] = $value;
                break;
            
            case 'login':
                $this->clientOptions['auth'][0] = $value;
                break;
            case 'password':
                $this->clientOptions['auth'][1] = $value;
                break;
            case 'auth-type':
                $this->clientOptions['auth'][2] = $value;
                break;

            case 'version':
                $this->setVersion($value);
                break;

            case 'features':
                $this->setFeatures($value);
                break;

            case 'uri':
                $this->setUri($value);
                break;
        }
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->clientOptions;
    }
    
    /**
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return isset($this->clientOptions[$key]) ? $this->clientOptions[$key] : $default;
    }
    
    /**
     * @param GuzzleClientInterface $httpClient
     * 
     * @return $this
     */
    public function setHttpClient(GuzzleClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        
        return $this;
    }

    /**
     * @return GuzzleClientInterface
     */
    public function getHttpClient()
    {
        if ($this->httpClient !== null) {
            return $this->httpClient;
        }

        if (class_exists(HandlerStack::class)) {
            $handler = HandlerStack::create();
            $handler->push(function (callable $handler) {
                return function (PsrRequestInterface $request, array $options) use ($handler) {
                    $this->last['request']         = (string)$request->getBody();
                    $this->last['requestHeaders']  = $this->formatHeadersAsString($request);
                    $this->last['response']        = null;
                    $this->last['responseHeaders'] = null;

                    return $handler($request, $options)->then(function (PsrResponseInterface $response) use ($request) {
                        $this->last['response'] = (string)$response->getBody();
                        $this->last['responseHeaders'] = $this->formatHeadersAsString($response);

                        return $response;
                    });
                };
            });

            $clientOptions = ['handler' => $handler];
        } else {
            $clientOptions = [];
        }

        return $this->httpClient = new GuzzleClient($clientOptions);
    }
    
    /**
     * @see ClientInterface::getLastRequest
     */
    public function getLastRequest()
    {
        return $this->last['request'];
    }

    /**
     * @see ClientInterface::getLastResponse
     */
    public function getLastResponse()
    {
        return $this->last['response'];
    }
    
    /**
     * @see ClientInterface::getLastRequestHeaders
     */
    public function getLastRequestHeaders()
    {
        return $this->last['requestHeaders'];
    }

    /**
     * @see ClientInterface::getLastResponseHeaders
     */
    public function getLastResponseHeaders()
    {
        return $this->last['responseHeaders'];
    }
    
    /**
     * Return a list of available functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [];
    }

    /**
     * Return a list of SOAP types
     *
     * @return array
     */
    public function getTypes()
    {
        return [];
    }
    
    /**
     * Retrieve last invoked method
     *
     * @return string
     */
    public function getLastMethod()
    {
        return $this->last['method'];
    }
    
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->call($name, $arguments);
    }
    
    /**
     *
     * @param string $name
     * @param mixed $arguments
     */
    public function notify($name, $arguments = array())
    {
        $this->isNotification = true;
        
        try {
            $this->call($name, $arguments);
        } catch (\Exception $e) {
            
        }
        
        $this->isNotification = false;
    }

    /**
     * @see ClientInterface::call
     * 
     * @param string $name
     * @param mixed  $arguments
     * @param bool   $notify
     *
     * @return mixed
     */
    public function call($name, $arguments = array())
    {
        $this->validate($name, $arguments);
        
        $client = $this->getHttpClient();

        $this->last['method'] = $name;
        $options = ['json' => $this->prepareRequest($name, $arguments, $this->isNotification)] + $this->clientOptions;

        if (method_exists($client, 'createRequest')) {
            // Guzzle 5
            $httpRequest = $client->createRequest('POST', $this->uri, $options);

            $this->last['request']         = (string)$httpRequest->getBody();
            $this->last['requestHeaders']  = $httpRequest->getHeadersAsString($httpRequest);
            $this->last['response']        = null;
            $this->last['responseHeaders'] = null;

            $httpResponse = $client->send($httpRequest);

            $this->last['response']        = (string)$httpResponse->getBody();
            $this->last['responseHeaders'] = $httpResponse->getHeadersAsString($httpResponse);
        } else {
            // Guzzle 7
            $httpRequest = new Request('POST', $this->uri);
            $httpResponse = $client->send($httpRequest, $options);
        }

        if ($this->isNotification) {
            return null;
        }

        if (method_exists($httpResponse, 'json')) {
            // Guzzle 5
            $content = $httpResponse->json(['object' => $this->features]);
        } else {
            // Guzzle 7
            $content = json_decode((string) $httpResponse->getBody(), !$this->features);
        }

        return $this->parseResponse($content);
    }

    /**
     * Fetch the Service Mapping Description (SMD) from the server
     *
     * @param string $smdUri
     *
     * @return array
     */
    public function getServiceMap($smdUri = null)
    {
        $client = $this->getHttpClient();
        $response = $client->get($smdUri ?: $this->uri, $this->clientOptions);

        if (method_exists($response, 'json')) {
            // Guzzle 5
            $this->serviceMap = $response->json();
        } else {
            // Guzzle 7
            $this->serviceMap = json_decode((string) $response->getBody());
        }

        return $this->serviceMap;
    }
    
    /**
     * Validate method
     *
     * @param string $name
     * @param array $arguments
     *
     * @throws \Exception if method is not found in definitions (only if autovalidate is set to true)
     */
    protected function validate($name, $arguments)
    {
        if ($this->autoValidate && isset($this->serviceMap)) {
            $definitions = null;

            if (isset($this->serviceMap['services'][$name])) {
                $definitions = $this->serviceMap['services'][$name];
            }

            if (!$definitions) {
                throw new \Exception('Method ' . $name . ' not found', -32601);
            }
        }
    }
    
    /**
     * Build json rpc request from RFC @see http://www.jsonrpc.org/specification
     * 
     * @param string $name
     * @param array $arguments
     * @param bool $notify
     * @return array rpc request
     */
    protected function prepareRequest($name, $arguments, $notify)
    {
        $request = array();

        if ($this->version !== null) {
            $request['jsonrpc'] = $this->version;
        }

        $request['method'] = $name;
        $request['params'] = (array)$arguments;

        if ($this->version === null && $notify) {
            $request['id'] = null;
        } elseif (!$notify) {
            $request['id'] = ++$this->id;
        }

        return $request;
    }

    /**
     * Parse the http response
     *
     * @param mixed $response
     *
     * @return mixed Rpc result
     *
     * @throws \Exception
     */
    protected function parseResponse($response)
    {
        $response = (array)$response;
        
        if (isset($response['error'])) {
            $error = (array)$response['error'];
            
            throw new \Exception((string) $error['message'], $error['code']);
        }
        
        if (empty($response['id'])) {
            throw new \Exception('No response ID in response');
        }

        if ($response['id'] != $this->id) {
            throw new \Exception('Invalid response ID #' . $this->id . ' != #' . $response['id']);
        }
            
        if (array_key_exists('result', $response)) {
            return $response['result'];
        }
        
        throw new \Exception('Invalid response: ' . $this->last['response']);
    }

    private function formatHeadersAsString(MessageInterface $message): string
    {
        $output = '';

        foreach ($message->getHeaders() as $name => $values) {
            $output .= $name . ': ' . implode(', ', $values) . "\r\n";
        }

        return $output;
    }
}
