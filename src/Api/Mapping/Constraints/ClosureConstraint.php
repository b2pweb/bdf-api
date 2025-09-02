<?php

namespace Bdf\Api\Mapping\Constraints;

use Bdf\Api\Mapping\ContextWrapperInterface;

/**
 * Class ClosureConstraint
 *
 * @package Bdf\Api\Mapping\Constraints
 */
class ClosureConstraint implements ConstraintInterface
{
    /**
     * @var callable
     */
    protected $callback;


    /**
     * ClosureConstraint constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, ContextWrapperInterface $context)
    {
        $callback = $this->callback;
        $callback($value, $context);
    }
}
