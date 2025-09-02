<?php

namespace Bdf\Api\Server\Request;

/**
 * @todo Attention le prepare relance le parseRawBody à chaque appel de prepare ou parseRawBody
 *
 * @package Bdf\Api\Server\Request
 */
abstract class AbstractInputRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected string $rawBody;
    
    /**
     * Requested method
     * @var string
     */
    protected ?string $method = null;

    protected array $params = [];

    public function __construct(?string $rawBody = null, bool $autoload = false)
    {
        $this->rawBody = $rawBody ?? file_get_contents('php://input');
        $this->rawBody = trim($this->rawBody);
        
        if ($autoload) {
            $this->parseRawBody();
        }
    }
    
    /**
     * @return string
     */
    public function getRawBody(): string
    {
        return $this->rawBody;
    }
    
    /**
     * Extract data from rawbody to load the request
     *
     * @return void
     */
    abstract protected function parseRawBody();
    
    /**
     * {@inheritdoc}
     */
    public function prepare(): void
    {
        $this->parseRawBody();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setMethod($method): void
    {
        $this->method = $method;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParams(): array
    {
        return $this->params;
    }
    
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getParam($index): mixed
    {
        if ($this->hasParam($index)) {
            return $this->params[$index];
        }

        return null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasParam($index): bool
    {
        return array_key_exists($index, $this->params);
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->getRawBody();
    }
}
