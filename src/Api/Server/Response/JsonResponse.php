<?php

namespace Bdf\Api\Server\Response;

use Bdf\Api\Server\Request\RequestInterface;

/**
 * 
 */
class JsonResponse implements ResponseInterface
{
    protected mixed $result;
    protected ?int $jsonOptions;

    public function __construct(?int $jsonOptions = null)
    {
        $this->jsonOptions = $jsonOptions;
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
        $this->result = $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getResult(): mixed
    {
        return $this->result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setError(\Exception $fault): void
    {
        $this->result = $fault->getMessage();
    }
    
    /**
     * {@inheritdoc}
     */
    public function send(): void
    {
        echo $this->__toString();
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if ($this->jsonOptions !== null) {
            return json_encode($this->result, $this->jsonOptions);
        }
        
        return json_encode($this->result);
    }
}
