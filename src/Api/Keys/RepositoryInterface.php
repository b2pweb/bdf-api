<?php

namespace Bdf\Api\Keys;

/**
 * 
 */
interface RepositoryInterface
{
    /**
     * Select a key
     * 
     * @param string $key
     *  
     * @return ApiKey
     */
    public function get($key);
}