<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface;

/**
 * Class ServiceMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class ServiceMetadata
{
    /**
     * @var MethodMetadata[]
     */
    protected $methods = [];

    /**
     * @var TypeMetadataInterface[]
     */
    protected $types = [];


    /**
     * @param string $name
     *
     * @return MethodMetadata
     *
     * @throws \Exception
     */
    public function getMethod($name)
    {
        if (!$this->hasMethod($name)) {
            throw new \Exception('Method "' . $name . '" not found');
        }

        return $this->methods[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasMethod($name)
    {
        return isset($this->methods[$name]);
    }

    /**
     * @param MethodMetadata $method
     *
     * @return $this
     */
    public function addMethod(MethodMetadata $method)
    {
        $this->methods[$method->getName()] = $method;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return TypeMetadataInterface
     *
     * @throws \Exception
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new \Exception('Type "' . $name . '" not found');
        }

        return $this->types[$name];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasType($name)
    {
        return isset($this->types[$name]);
    }

    /**
     * @param TypeMetadataInterface $type
     *
     * @return $this
     */
    public function addType(TypeMetadataInterface $type)
    {
        $this->types[$type->getName()] = $type;

        return $this;
    }
}
