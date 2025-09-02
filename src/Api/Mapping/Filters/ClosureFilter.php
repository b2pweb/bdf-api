<?php

namespace Bdf\Api\Mapping\Filters;

use Bdf\Api\Mapping\ContextWrapperInterface;

/**
 * Class ClosureFilter
 *
 * @package Bdf\Api\Mapping\Filters
 */
class ClosureFilter implements FilterInterface
{
    /**
     * @var callable
     */
    protected $callback;


    /**
     * ClosureFilter constructor.
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
    public function filter($value, ContextWrapperInterface $context)
    {
        $callback = $this->callback;
        return $callback($value, $context);
    }
}
