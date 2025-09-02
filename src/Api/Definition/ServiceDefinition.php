<?php

namespace Bdf\Api\Definition;

/**
 * Class ServiceDefinition
 *
 * @package Bdf\Api\Definition
 */
class ServiceDefinition
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $namespace;

    /**
     * @var MethodDefinition[]
     */
    protected $methods = [];

    /**
     * @var TypeDefinition[]
     */
    protected $types = [];


    /**
     * ServiceDefinition constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ServiceDefinition
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the service namespace
     * If not defined, the API URI should be used as namespace
     *
     * Define a namespace permit to request on an other URL without reimport the WSDL (i.e. sandbox -> prod)
     *
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Define the namespace
     *
     * @param string $namespace
     *
     * @return ServiceDefinition
     */
    public function setNamespace(string $namespace): ServiceDefinition
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return MethodDefinition[]
     */
    public function getMethods()
    {
        return $this->methods;
    }
    
    /**
     * @param MethodDefinition[] $methods
     *
     * @return ServiceDefinition
     */
    public function setMethods(array $methods)
    {
        $this->methods = [];

        foreach ($methods as $method) {
            $this->addMethod($method);
        }

        return $this;
    }

    /**
     * @param string $name
     * 
     * @return MethodDefinition
     */
    public function getMethod($name)
    {
        if (!$this->hasMethod($name)) {
            return;
        }

        return $this->methods[$name];
    }

    /**
     * @param string $name
     * 
     * @return boolean
     */
    public function hasMethod($name)
    {
        return isset($this->methods[$name]);
    }

    /**
     * @param MethodDefinition $method
     * 
     * @return ServiceDefinition
     */
    public function addMethod(MethodDefinition $method)
    {
        $this->methods[$method->getName()] = $method;

        return $this;
    }

    /**
     * @return TypeDefinition[]
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param array $types
     *
     * @return ServiceDefinition
     */
    public function setTypes(array $types)
    {
        $this->types = [];

        foreach ($types as $type) {
            $this->addType($type);
        }

        return $this;
    }

    /**
     * @param string $type
     * 
     * @return TypeDefinition|null
     */
    public function getType($type)
    {
        if (!$this->hasType($type)) {
            return null;
        }

        return $this->types[$type];
    }

    /**
     * @param string $type
     * 
     * @return boolean
     */
    public function hasType($type)
    {
        return isset($this->types[$type]);
    }

    /**
     * @param TypeDefinition $type
     * 
     * @return ServiceDefinition
     */
    public function addType(TypeDefinition $type)
    {
        $this->types[$type->getName()] = $type;

        return $this;
    }
}
