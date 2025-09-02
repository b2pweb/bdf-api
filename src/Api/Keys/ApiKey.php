<?php

namespace Bdf\Api\Keys;

/**
 * 
 */
class ApiKey
{
    protected ?string $key;
    protected string $ownerName = 'unknown owner';
    protected bool $valid = true;
    
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        if (isset($attributes['key'])) {
            $this->setKey($attributes['key']);
        }

        if (isset($attributes['ownerName'])) {
            $this->setOwnerName($attributes['ownerName']);
        }

        if (isset($attributes['valid'])) {
            $this->valid = (bool) $attributes['valid'];
        }
    }
    
    public function setKey(string $key): void
    {
        $this->key = $key;
    }
    
    public function getKey(): ?string
    {
        return $this->key;
    }
    
    public function setOwnerName(string $ownerName): void
    {
        $this->ownerName = $ownerName;
    }
    
    public function getOwnerName(): string
    {
        return $this->ownerName;
    }
    
    public function isValid(): bool
    {
        return $this->valid;
    }
}
