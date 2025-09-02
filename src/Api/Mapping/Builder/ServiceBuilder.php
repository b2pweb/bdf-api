<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Exception\InvalidArgumentException;
use Bdf\Api\Mapping\Metadata\ServiceMetadata;
use Bdf\Api\Mapping\Metadata\Types\ArrayTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\OptionTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\SimpleTypeMetadata;
use Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface;
use Bdf\Api\Mapping\Registry;

/**
 * Class ServiceBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class ServiceBuilder
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ServiceMetadata
     */
    protected $metadata;

    /**
     * @var MethodBuilder[]
     */
    protected $methods = [];

    /**
     * @var TypeBuilder[]
     */
    protected $types = [];

    /**
     * @var TypeMetadataInterface[]
     */
    protected $buildedTypes = [];


    /**
     * ServiceBuilder constructor.
     *
     * @param Registry|null $registry
     */
    public function __construct(?Registry $registry = null)
    {
        $this->registry = $registry ?? new Registry();
        $this->metadata = new ServiceMetadata();
    }

    /**
     * @param string $name
     * @param string $class
     * @param callable $initializer
     *
     * @return $this
     */
    public function type($name, $class, callable $initializer = null)
    {
        $builder = new TypeBuilder($this->registry);

        $builder->name($name);
        $builder->className($class);

        if ($initializer !== null) {
            $initializer($builder);
        }

        $this->types[$name] = $builder;

        return $this;
    }

    /**
     * @param string $name
     * @param callable $initializer
     *
     * @return $this
     */
    public function method($name, callable $initializer = null)
    {
        $builder = new MethodBuilder($this->registry);

        $builder->name($name);

        if ($initializer !== null) {
            $initializer($builder);
        }

        $this->methods[$name] = $builder;

        return $this;
    }

    /**
     * @return ServiceMetadata
     */
    public function build()
    {
        foreach ($this->types as $typeName => $typeBuilder) {
            $this->buildType($typeName);
        }

        foreach ($this->methods as $builder) {
            $this->metadata->addMethod($builder->build($this));
        }

        return $this->metadata;
    }

    /**
     * @param string|array $typeName
     *
     * @return TypeMetadataInterface
     *
     * @throws InvalidArgumentException
     */
    public function buildType($typeName)
    {
        if (empty($this->buildedTypes[$typeName])) {
            if ($this->isSimpleType($typeName)) {
                $metadata = new SimpleTypeMetadata($typeName);
            } elseif ($this->isArrayType($typeName)) {
                $metadata = new ArrayTypeMetadata($this->buildType(substr($typeName, 0, -2)));
            } elseif ($this->isOptionType($typeName)) {
                $metadata = new OptionTypeMetadata();
            } elseif (isset($this->types[$typeName])) {
                $metadata = $this->types[$typeName]->build($this);
            } else {
                throw new InvalidArgumentException('The mapping type "' . $typeName . '" is not defined');
            }

            $this->metadata->addType($metadata);

            $this->buildedTypes[$typeName] = $metadata;
        }

        return $this->buildedTypes[$typeName];
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isSimpleType($typeName)
    {
        return in_array($typeName, [
            'array',
            'bool', 'boolean',
            'double', 'float',
            'int', 'integer', 'long',
            'mixed',
            'null',
            'object',
            'string', 'str',
            'void'
        ]);
    }

    /**
     * @param string $typeName
     *
     * @return boolean
     */
    protected function isArrayType($typeName)
    {
        return $typeName && strrpos($typeName, '[]') === (strlen($typeName) - 2);
    }

    /**
     * @param string $typeName
     *
     * @return boolean
     */
    protected function isOptionType($typeName)
    {
        return $typeName === 'options';
    }
}
