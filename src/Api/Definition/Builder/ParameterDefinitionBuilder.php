<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ParameterDefinition;

/**
 * Class ParameterDefinitionBuilder
 *
 * @package Bdf\Api\Definition\Builder
 */
class ParameterDefinitionBuilder
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @var bool
     */
    protected $optional = false;

    /**
     * @var string
     */
    protected $description;


    /**
     * @param string $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param bool $optional
     *
     * @return $this
     */
    public function optional($optional = true)
    {
        $this->optional = $optional;

        return $this;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function defaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function description($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Cast type as array
     *
     * @return $this
     */
    public function collection()
    {
        if (!strrpos($this->type, '[]')) {
            $this->type .= '[]';
        }

        return $this;
    }

    /**
     * @param ServiceDefinitionBuilder $serviceDefinition
     *
     * @return ParameterDefinition
     */
    public function build(ServiceDefinitionBuilder $serviceDefinition)
    {
        $definition = new ParameterDefinition($this->name, $serviceDefinition->buildType($this->type));
        $definition->setDefaultValue($this->defaultValue);
        $definition->setDescription($this->description);
        $definition->setOptional($this->optional);

        return $definition;
    }
}
