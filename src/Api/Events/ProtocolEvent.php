<?php

namespace Bdf\Api\Events;

use Bdf\Api\ApiRequest;
use Bdf\Api\Protocol\ProtocolInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

/**
 * ProtocolEvent
 */
class ProtocolEvent extends KernelEvent
{
    protected ProtocolInterface $protocol;
    
    /**
     * Constructor
     * 
     * @param HttpKernelInterface $kernel
     * @param ProtocolInterface   $protocol
     * @param ApiRequest          $request
     * @param string              $requestType
     */
    public function __construct(HttpKernelInterface $kernel, ProtocolInterface $protocol, ApiRequest $request, string $requestType)
    {
        parent::__construct($kernel, $request, $requestType);
        
        $this->protocol = $protocol;
    }
    
    /**
     * Returns the selected api
     * 
     * @return ProtocolInterface
     * 
     * @api
     */
    public function getProtocol(): ProtocolInterface
    {
        return $this->protocol;
    }
}
