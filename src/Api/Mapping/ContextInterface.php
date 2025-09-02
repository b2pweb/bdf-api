<?php

namespace Bdf\Api\Mapping;

use Psr\Container\ContainerInterface;

/**
 * Interface ContextInterface
 *
 * @package Bdf\Api\Mapping
 */
interface ContextInterface
{
    public function di(): ContainerInterface;
    public function setDI(ContainerInterface $di);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options);

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function hasOption($name);

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null);

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption($name, $value);
}
