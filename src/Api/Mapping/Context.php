<?php

namespace Bdf\Api\Mapping;

use Psr\Container\ContainerInterface;

/**
 * Class Context
 *
 * @package Bdf\Api\Mapping
 */
class Context implements ContextInterface, \ArrayAccess
{
    protected ContainerInterface $di;
    protected array $options = [];

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    public function di(): ContainerInterface
    {
        return $this->di;
    }

    public function setDI(ContainerInterface $di)
    {
        $this->di = $di;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        return $this->hasOption($name) ? $this->options[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Does the service exists
     */
    public function offsetExists($offset): bool
    {
        return $this->di->has($offset);
    }

    /**
     * Get service from DI
     *
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->di->get($offset);
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
