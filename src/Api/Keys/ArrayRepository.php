<?php

namespace Bdf\Api\Keys;

/**
 * Class ArrayRepository
 * @package Bdf\Api\Keys
 */
class ArrayRepository implements RepositoryInterface
{
    /**
     * @var array
     */
    protected array $keys;
    
    /**
     * @param array  $keys
     * @param array-key|null $arrayKey
     */
    public function __construct(array $keys = [], string|int|null $arrayKey = null)
    {
        if ($arrayKey !== null && isset($keys[$arrayKey])) {
            $keys = $keys[$arrayKey];
        }

        $this->keys = $keys;
    }
    
    /**
     * {@inheritdoc}
     */
    public function get($key): ApiKey
    {
        if (!isset($this->keys[$key])) {
            return new ApiKey([
                'key'   => $key,
                'valid' => false,
            ]);
        }

        return new ApiKey([
            'key'       => $key,
            'ownerName' => $this->keys[$key],
        ]);
    }
}