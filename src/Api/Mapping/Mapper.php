<?php

namespace Bdf\Api\Mapping;

use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Mapping\Metadata\ServiceMetadata;
use Psr\Container\ContainerInterface;

/**
 * Class Mapper
 *
 * @package Bdf\Api\Mapping
 */
class Mapper implements MapperInterface
{
    protected ContainerInterface $container;
    protected ServiceMetadata $serviceMetadata;
    protected Context $context;

    public function __construct(ContainerInterface $container, ServiceMetadata $serviceMetadata)
    {
        $this->container = $container;
        $this->serviceMetadata = $serviceMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function mapMethod(MethodDefinition $methodDefinition, array $parameters): array
    {
        return $this->serviceMetadata->getMethod($methodDefinition->getName())->map($parameters, $this->context = new Context($this->container));
    }

    /**
     * {@inheritdoc}
     */
    public function mapMethodReturn(MethodDefinition $methodDefinition, $value): mixed
    {
        return $this->serviceMetadata->getMethod($methodDefinition->getName())->unmap($value, $this->context);
    }
}
