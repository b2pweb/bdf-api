<?php

namespace Bdf\Api\Events;

use Bdf\Api\Api;
use Bdf\Api\ApiRequest;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

/**
 * Class InitializationEvent
 *
 * @package Bdf\Api\Events
 */
class ApiEvent extends KernelEvent
{
    protected Api $api;
    
    /**
     * Constructor
     * 
     * @param HttpKernelInterface $kernel
     * @param Api                 $api
     * @param ApiRequest          $request
     * @param string              $requestType
     */
    public function __construct(HttpKernelInterface $kernel, Api $api, ApiRequest $request, string $requestType)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->api = $api;
    }
    
    /**
     * Returns the selected api
     * 
     * @return Api
     * 
     * @api
     */
    public function getApi(): Api
    {
        return $this->api;
    }
}
