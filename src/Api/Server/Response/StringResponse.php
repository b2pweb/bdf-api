<?php

namespace Bdf\Api\Server\Response;

use Bdf\Api\Server\Request\RequestInterface;

/**
 * Class StringResponse
 * 
 * @package Bdf\Api\Server\Response
 */
class StringResponse implements ResponseInterface
{
    protected ?string $result;
    
    public function __construct(?string $result = null)
    {
        $this->setResult($result);
    }
    
    /**
     * {@inheritdoc}
     */
    public function prepare(RequestInterface $request): void
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function setResult($result): void
    {
        if (!is_string($result)) {
            $result = print_r($result, true);
        }
        
        $this->result = $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getResult(): ?string
    {
        return $this->result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setError(\Exception $fault): void
    {
        $this->setResult($fault->getMessage());
    }
    
    /**
     * {@inheritdoc}
     */
    public function send(): void
    {
        echo $this->result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return (string) $this->result;
    }
}
