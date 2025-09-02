<?php

namespace Bdf\Api\Server\Request;

/**
 * 
 */
class CustomRequest implements RequestInterface
{
    protected ?string $method;
    protected array $params = [];

    public function __construct(?string $method = null, array $params = [])
    {
        $this->setMethod($method);
        $this->setParams($params);
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::prepare
     */
    public function prepare(): void
    {
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::validate
     */
    public function validate(): void
    {
        if (empty($this->method)) {
            throw new \Exception('Invalid method name');
        }
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::setMethod
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::getMethod
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::setParams
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::getParams
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::addParam
     */
    public function addParam($value, $key = null): void
    {
        if ((null === $key) || !is_string($key)) {
            $this->params[count($this->params)] = $value;
        } else {
            $this->params[$key] = $value;
        }
    }

    /**
     * @see Bdf\Api\Server\Request\RequestInterface::getParam
     */
    public function getParam($index): mixed
    {
        if ($this->hasParam($index)) {
            return $this->params[$index];
        }

        return null;
    }
    
    /**
     * @see Bdf\Api\Server\Request\RequestInterface::hasParam
     */
    public function hasParam($index): bool
    {
        return array_key_exists($index, $this->params);
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->method . ':' . implode(',', $this->params);
    }
}
