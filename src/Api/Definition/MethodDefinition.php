<?php

namespace Bdf\Api\Definition;

/**
 * Class MethodDefinition
 *
 * @package Bdf\Api\Definition
 */
class MethodDefinition
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ParameterDefinition[]
     */
    protected $parameters = [];

    /**
     * @var TypeDefinition|null
     */
    protected $return;

    /**
     * @var bool
     */
    protected $throwingException = false;


    /**
     * MethodDefinition constructor.
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ParameterDefinition[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param ParameterDefinition[] $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = [];

        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }

        return $this;
    }

    /**
     * @param ParameterDefinition $parameter
     *
     * @return self
     */
    public function addParameter(ParameterDefinition $parameter)
    {
        $parameter->setPosition(count($this->parameters));

        $this->parameters[] = $parameter;

        return $this;
    }

    /**
     * @return TypeDefinition|null
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param TypeDefinition|null $return
     *
     * @return self
     */
    public function setReturn($return)
    {
        $this->return = $return;

        return $this;
    }

    /**
     * @return bool
     */
    public function isThrowingException()
    {
        return (bool) $this->throwingException;
    }

    /**
     * @param bool $throwingException
     * 
     * @return self
     */
    public function setThrowingException($throwingException)
    {
        $this->throwingException = (bool) $throwingException;

        return $this;
    }
}
