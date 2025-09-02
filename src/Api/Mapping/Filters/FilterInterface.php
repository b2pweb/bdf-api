<?php

namespace Bdf\Api\Mapping\Filters;

use Bdf\Api\Mapping\ContextWrapperInterface;

/**
 * FilterInterface
 *
 * @package Bdf\Api\Mapping\Filters
 */
interface FilterInterface
{
    /**
     * Filter a api value
     *
     * @param mixed $value
     * @param ContextWrapperInterface $context
     *
     * @return mixed     Returns the filtered value
     */
    public function filter($value, ContextWrapperInterface $context);
}
