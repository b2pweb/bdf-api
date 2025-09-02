<?php

namespace Bdf\Api\Mapping;

/**
 * Class ComplexTypeContext
 *
 * @package Bdf\Api\Mapping
 */
class ComplexTypeContext extends AbstractContextWrapper
{
    /**
     * @var mixed
     */
    protected $source;

    /**
     * @var mixed
     */
    protected $target;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var callable
     */
    protected $onPropertyNotProcessed;


    /**
     * ComplexTypeContext constructor.
     *
     * @param ContextInterface $wrappedContext
     * @param mixed $source
     * @param mixed $target
     * @param callable $onPropertyNotProcessed
     */
    public function __construct(ContextInterface $wrappedContext, $source, $target, callable $onPropertyNotProcessed)
    {
        parent::__construct($wrappedContext);

        $this->source = $source;
        $this->target = $target;
        $this->onPropertyNotProcessed = $onPropertyNotProcessed;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->properties[$name];
        }

        $callback = $this->onPropertyNotProcessed;
        $callback($name, $this);

        return $this->properties[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->properties[$name] = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }
}
