<?php

namespace Bdf\Api\Mapping;

use Psr\Container\ContainerInterface;

/**
 * Class AbstractContextWrapper
 *
 * @package Bdf\Api\Mapping
 */
abstract class AbstractContextWrapper implements ContextWrapperInterface, \ArrayAccess
{
    /**
     * @var ContextInterface
     */
    protected $wrappedContext;

    /**
     * AbstractContextWrapper constructor.
     *
     * @param ContextInterface $wrappedContext
     */
    public function __construct(ContextInterface $wrappedContext)
    {
        $this->wrappedContext = $wrappedContext;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->wrappedContext->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->wrappedContext->setOptions($options);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return $this->wrappedContext->hasOption($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return $this->wrappedContext->getOption($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        $this->wrappedContext->setOption($name, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function di(): ContainerInterface
    {
        return $this->wrappedContext->di();
    }

    /**
     * {@inheritdoc}
     */
    public function setDI(ContainerInterface $di)
    {
        $this->wrappedContext->setDI($di);

        return $this;
    }

    /**
     * Does the service exists
     */
    public function offsetExists($offset): bool
    {
        return isset($this->wrappedContext[$offset]);
    }

    /**
     * Get service from DI
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->wrappedContext[$offset];
    }

    /**
     * Do nothing
     */
    public function offsetSet($offset, $value): void
    {

    }

    /**
     * Do nothing
     */
    public function offsetUnset($offset): void
    {

    }
}
