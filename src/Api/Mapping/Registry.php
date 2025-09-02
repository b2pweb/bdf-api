<?php

namespace Bdf\Api\Mapping;

use Bdf\Api\Mapping\Constraints\ClosureConstraint;
use Bdf\Api\Mapping\Constraints\ConstraintInterface;
use Bdf\Api\Mapping\Filters\ClosureFilter;
use Bdf\Api\Mapping\Filters\FilterInterface;
use Bdf\Api\Mapping\Transformers\TransformerInterface;
use Closure;
use ReflectionClass;

/**
 * Registry
 *
 * @package Bdf\Api\Mapping
 */
class Registry
{
    /**
     * Constraint namespaces
     *
     * @var array
     */
    protected $constraintNamespaces = [
        'Bdf\Api\Mapping\Constraints\\',
    ];

    /**
     * Filter namespaces
     *
     * @var array
     */
    protected $filterNamespaces = [
        'Bdf\Api\Mapping\Filters\\',
    ];

    /**
     * Transformer namespaces
     *
     * @var array
     */
    protected $transformerNamespaces = [
        'Bdf\Api\Mapping\Transformers\\',
    ];


    /**
     * Register a constraint namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function registerConstraintNamespace($namespace)
    {
        $this->constraintNamespaces[] = rtrim($namespace, '\\').'\\';

        return $this;
    }

    /**
     * Get the constraint instance
     *
     * @param mixed $constraint
     *
     * @return ConstraintInterface
     */
    public function getConstraint($constraint)
    {
        if ($constraint instanceof ConstraintInterface) {
            return $constraint;
        }

        // Utilisation d'instanceof Closure au lieu de is_callable car si $constraint est une string avec le nom d'une fonction existante, il serait callable...
        if ($constraint instanceof Closure) {
            return new ClosureConstraint($constraint);
        }

        $options = [];

        if (is_array($constraint)) {
            $options = $constraint;
            $constraint = array_shift($options);
        }

        foreach ($this->constraintNamespaces as $namespace) {
            $className = $namespace . ucfirst($constraint) . 'Constraint';

            if (class_exists($className)) {
                return (new ReflectionClass($className))->newInstanceArgs($options);
            }
        }

        return (new ReflectionClass($constraint))->newInstanceArgs($options);
    }

    /**
     * Register a filter namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function registerFilterNamespace($namespace)
    {
        $this->filterNamespaces[] = rtrim($namespace, '\\').'\\';

        return $this;
    }

    /**
     * Get a filter instance
     *
     * @param mixed $filter
     *
     * @return FilterInterface
     */
    public function getFilter($filter)
    {
        if ($filter instanceof FilterInterface) {
            return $filter;
        }

        // Utilisation d'instanceof Closure au lieu de is_callable car si $filter est une string avec le nom d'une fonction existante, il serait callable...
        if ($filter instanceof Closure) {
            return new ClosureFilter($filter);
        }

        $options = [];

        if (is_array($filter)) {
            $options = $filter;
            $filter = array_shift($options);
        }

        foreach ($this->filterNamespaces as $namespace) {
            $className = $namespace . ucfirst($filter) . 'Filter';

            if (class_exists($className)) {
                return (new ReflectionClass($className))->newInstanceArgs($options);
            }
        }

        return (new ReflectionClass($filter))->newInstanceArgs($options);
    }

    /**
     * Register a transformer namespace
     *
     * @param string $namespace
     *
     * @return $this
     */
    public function registerTransformerNamespace($namespace)
    {
        $this->transformerNamespaces[] = rtrim($namespace, '\\').'\\';

        return $this;
    }

    /**
     * Get a transformer instance
     *
     * @param mixed $transformer
     *
     * @return TransformerInterface
     */
    public function getTransformer($transformer)
    {
        if ($transformer instanceof TransformerInterface) {
            return $transformer;
        }

        $options = [];

        if (is_array($transformer)) {
            $options = $transformer;
            $transformer = array_shift($options);
        }

        foreach ($this->transformerNamespaces as $namespace) {
            $className = $namespace . ucfirst($transformer) . 'Transformer';

            if (class_exists($className)) {
                return (new ReflectionClass($className))->newInstanceArgs($options);
            }
        }

        return (new ReflectionClass($transformer))->newInstanceArgs($options);
    }
}
