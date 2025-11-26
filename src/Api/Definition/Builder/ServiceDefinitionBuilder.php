<?php

namespace Bdf\Api\Definition\Builder;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\ServiceDefinition;
use Bdf\Api\Definition\SimpleTypeDefinition;
use Bdf\Api\Definition\TypeDefinition;
use Bdf\Api\Exception\InvalidArgumentException;

/**
 * ServiceDefinitionBuilder
 *
 * @package Bdf\Api\Definition\Builder
 */
class ServiceDefinitionBuilder
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
     * @var MethodDefinitionBuilder[]
     */
    protected $methods = [];

    /**
     * @var ComplexTypeDefinitionBuilder[]
     */
    protected $types = [];

    /**
     * @var array
     */
    protected $simpleTypes = [
        'array',
        'bool', 'boolean',
        'double', 'float',
        'int', 'integer', 'long',
        'mixed',
        'null',
        'object',
        'string', 'str',
        'void'
    ];

    /**
     * @var TypeDefinition[]
     */
    protected $buildedTypes = [];


    /**
     * Set the service name.
     *
     * Will be use by renderer
     *
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
     * Define the service namespace
     * If not defined, the API URI should be used as namespace
     *
     * Define a namespace permit to request on an other URL without reimport the WSDL (i.e. sandbox -> prod)
     *
     * @param string $namespace
     *
     * @return ServiceDefinitionBuilder
     */
    public function namespace(string $namespace): ServiceDefinitionBuilder
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Add a method
     *
     * @param string $name
     * @param callable|null $initializer
     *
     * @return $this
     */
    public function method($name, ?callable $initializer = null)
    {
        $builder = new MethodDefinitionBuilder();

        $builder->name($name);

        if ($initializer !== null) {
            $initializer($builder);
        }

        $this->methods[$name] = $builder;

        return $this;
    }

    /**
     * Add a complex type
     *
     * @param string $name
     * @param callable|null $initializer
     *
     * @return $this
     */
    public function complexType($name, ?callable $initializer = null)
    {
        $builder = new ComplexTypeDefinitionBuilder();

        $builder->name($name);

        if ($initializer !== null) {
            $initializer($builder);
        }

        $this->types[$name] = $builder;

        return $this;
    }

    /**
     * @return ServiceDefinition
     */
    public function build()
    {
        $this->buildedTypes = [];

        $definition = new ServiceDefinition($this->name);

        if ($this->namespace) {
            $definition->setNamespace($this->namespace);
        }

        foreach ($this->types as $typeName => $typeBuilder) {
            $definition->addType($this->buildType($typeName));
        }

        foreach ($this->methods as $methodBuilder) {
            $definition->addMethod($methodBuilder->build($this));
        }

        return $definition;
    }

    /**
     * @param string $typeName
     *
     * @return TypeDefinition
     */
    public function buildType($typeName)
    {
        if (!$this->isTypeBuilt($typeName)) {
            if ($this->isComplexType($typeName)) {
                $definition = $this->types[$typeName]->build($this);
            } elseif ($this->isSimpleType($typeName)) {
                $definition = new SimpleTypeDefinition($typeName);
            } elseif ($this->isArrayType($typeName)) {
                $definition = new ArrayTypeDefinition($this->buildType(substr($typeName, 0, -2)));
            } else {
                throw new InvalidArgumentException('The definition type "' . $typeName . '" is not defined');
            }

            $this->buildedTypes[$typeName] = $definition;
        }

        return $this->buildedTypes[$typeName];
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isTypeBuilt($typeName)
    {
        return $typeName !== null && isset($this->buildedTypes[$typeName]);
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isSimpleType($typeName)
    {
        return in_array($typeName, $this->simpleTypes);
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isArrayType($typeName)
    {
        return $typeName && strrpos($typeName, '[]') === (strlen($typeName) - 2);
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isComplexType($typeName)
    {
        return $typeName !== null && isset($this->types[$typeName]);
    }
}
