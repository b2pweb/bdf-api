<?php

namespace Bdf\Soap\Renderer\Strategy;

use Bdf\Api\Definition\TypeDefinition;
use Bdf\Soap\Renderer\Wsdl;

/**
 * @package Bdf\Soap\Renderer\Strategy
 */
abstract class AbstractComplexTypeStrategy implements ComplexTypeStrategyInterface
{
    /**
     * @var Wsdl|null Context object
     */
    protected ?Wsdl $context = null;

    /**
     * Set the WSDL Context object this strategy resides in.
     *
     * @param Wsdl $context
     */
    public function setContext(Wsdl $context): void
    {
        $this->context = $context;
    }

    /**
     * Return the current WSDL context object
     *
     * @return Wsdl|null
     */
    public function getContext(): ?Wsdl
    {
        return $this->context;
    }

    /**
     * Look through registered types
     *
     * @param TypeDefinition $type
     * 
     * @return string|null
     */
    public function scanRegisteredTypes(TypeDefinition $type): ?string
    {
        if (array_key_exists($type->getName(), $this->getContext()->getTypes())) {
            return $this->getContext()->getTypes()[$type->getName()];
        }
        
        return null;
    }
}
