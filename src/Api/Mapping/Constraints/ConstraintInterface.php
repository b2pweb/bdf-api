<?php

namespace Bdf\Api\Mapping\Constraints;

use Bdf\Api\Mapping\ContextWrapperInterface;

/**
 * Class ConstraintInterface
 *
 * @package Bdf\Api\Mapping\Constraints
 */
interface ConstraintInterface
{
    /**
     * Validate a domain value
     *
     * @param mixed $value
     * @param ContextWrapperInterface $context
     */
    public function validate($value, ContextWrapperInterface $context);
}
