<?php

namespace Bdf\Api\Mapping;

/**
 * Interface ContextWrapperInterface
 *
 * @package Bdf\Api\Mapping
 */
interface ContextWrapperInterface extends ContextInterface
{
    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     *
     * @return boolean
     */
    public function has($name);

    /**
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value);
}
