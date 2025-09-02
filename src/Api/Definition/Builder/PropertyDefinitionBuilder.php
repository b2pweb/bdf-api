<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\PropertyDefinition;

/**
 * PropertyDefinitionBuilder
 *
 * @package Bdf\Api\Definition\Builder
 */
class PropertyDefinitionBuilder
{
    /**
     * Name of the property
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $nillable = true;

    /**
     * @var bool
     */
    protected $optional = false;


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
     * @param bool $nillable
     *
     * @return $this
     */
    public function nillable($nillable = true)
    {
        $this->nillable = (bool) $nillable;

        return $this;
    }

    /**
     * @param bool $optional
     *
     * @return $this
     */
    public function optional($optional = true)
    {
        $this->optional = (bool) $optional;

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
     * @param ServiceDefinitionBuilder $serviceBuilder
     *
     * @return PropertyDefinition
     */
    public function build(ServiceDefinitionBuilder $serviceBuilder)
    {
        $definition = new PropertyDefinition($this->name, $serviceBuilder->buildType($this->type));

        $definition->setNillable($this->nillable);
        $definition->setOptional($this->optional);

        return $definition;
    }
}
