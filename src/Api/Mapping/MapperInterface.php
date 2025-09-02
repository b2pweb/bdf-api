<?php

namespace Bdf\Api\Mapping;

use Bdf\Api\Definition\MethodDefinition;

/**
 * Interface MapperInterface
 *
 * @package Bdf\Api\Mapping
 */
interface MapperInterface
{
    /**
     * @param MethodDefinition $methodDefinition
     * @param array $parameters
     *
     * @return array
     */
    public function mapMethod(MethodDefinition $methodDefinition, array $parameters);

    /**
     * @param MethodDefinition $methodDefinition
     * @param mixed $value
     *
     * @return mixed
     */
    public function mapMethodReturn(MethodDefinition $methodDefinition, $value);
}
