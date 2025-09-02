<?php

namespace Bdf\JsonRpc\Server;

use Bdf\Api\Server\Request\AbstractInputRequest;
use Bdf\JsonRpc\Exception;

/**
 * 
 */
class Request extends AbstractInputRequest
{
    /**
     * Request ID
     * @var mixed
     */
    protected $id;

    /**
     * Flag
     * @var bool
     */
    protected $isMethodError = false;

    /**
     * Regex for method
     * @var string
     */
    protected $methodRegex = '/^[a-z][a-z0-9_.]*$/i';

    /**
     * JSON-RPC version of request
     * @var string
     */
    protected $version = '1.0';


    /**
     * @see Bdf\Api\Server\Request\RequestInterface::validate
     */
    public function validate()
    {
        if (!$this->isMethodError() && (null === $this->getMethod())) {
            throw new Exception\InvalidArgumentException('Invalid Request', -32600);
        }

        if ($this->isMethodError()) {
            throw new Exception\InvalidArgumentException('Invalid Request', -32600);
        }
    }
    
    /**
     * Set request state
     *
     * @param  array $options
     *
     * @return self
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            } elseif ($key == 'jsonrpc') {
                $this->setVersion($value);
            }
        }
        
        return $this;
    }

    /**
     * @see Bdf\Api\Server\Request\RequestInterface::setMethod
     */
    public function setMethod($method): void
    {
        if (!preg_match($this->methodRegex, $method)) {
            $this->isMethodError = true;
        } else {
            $this->method = $method;
        }
    }

    /**
     * Was a bad method provided?
     *
     * @return bool
     */
    public function isMethodError()
    {
        return $this->isMethodError;
    }

    /**
     * Set request identifier
     *
     * @param  mixed $name
     */
    public function setId($name)
    {
        $this->id = (string) $name;
    }

    /**
     * Retrieve request identifier
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set JSON-RPC version
     *
     * @param  string $version
     */
    public function setVersion($version)
    {
        if ('2.0' == $version) {
            $this->version = '2.0';
        } else {
            $this->version = '1.0';
        }
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
     * Cast request to JSON
     *
     * @return string
     */
    public function toJson()
    {
        $jsonArray = array(
            'method' => $this->getMethod()
        );
        
        if (null !== ($id = $this->getId())) {
            $jsonArray['id'] = $id;
        }
        
        $params = $this->getParams();
        
        if (!empty($params)) {
            $jsonArray['params'] = $params;
        }
        
        if ('2.0' == $this->getVersion()) {
            $jsonArray['jsonrpc'] = '2.0';
        }

        return json_encode($jsonArray);
    }
    
    /**
     * Cast request to string (JSON)
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function parseRawBody(): void
    {
        $this->setOptions((array) json_decode($this->rawBody, true));
    }
}
