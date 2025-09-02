<?php

namespace Bdf\Api\Keys;

/**
 * 
 */
class NullRepository implements RepositoryInterface
{
    /**
     * @see RepositoryInterface::get
     */
    public function get($key): ApiKey
    {
        return new ApiKey([
            'key' => $key,
        ]);
    }
}