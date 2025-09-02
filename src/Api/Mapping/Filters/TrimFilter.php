<?php

namespace Bdf\Api\Mapping\Filters;

use Bdf\Api\Mapping\ContextWrapperInterface;

/**
 * Class TrimFilter
 *
 * @package Bdf\Api\Mapping\Filters
 */
class TrimFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $mask;


    /**
     * TrimFilter constructor.
     *
     * @param string $mask
     */
    public function __construct($mask = null)
    {
        $this->mask = $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value, ContextWrapperInterface $context)
    {
        return $this->mask !== null ? trim($value, $this->mask) : trim($value);
    }
}
