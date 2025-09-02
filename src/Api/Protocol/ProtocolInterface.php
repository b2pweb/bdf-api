<?php

namespace Bdf\Api\Protocol;

use Bdf\Api\ApiRequest;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * ProtocolInterface
 */
interface ProtocolInterface
{
    /**
     * Handle an api service request
     * 
     * @param ApiRequest $request
     *
     * @return BaseResponse
     */
    public function handle(ApiRequest $request);

    /**
     * Check whether the request is a service map request
     * 
     * @param ApiRequest $request
     *
     * @return boolean
     */
    public function isServiceMapRequest(ApiRequest $request);
}
