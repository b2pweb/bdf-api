<?php

namespace Bdf\Soap\Renderer\Strategy;

use Bdf\Api\Definition\TypeDefinition;
use Bdf\Soap\Renderer\Wsdl;

/**
 * Interface strategies that generate an XSD-Schema for complex data types in WSDL files.
 * 
 * @package Bdf\Soap\Renderer\Strategy
 */
interface ComplexTypeStrategyInterface
{
    /**
     * Method accepts the current WSDL context file.
     *
     * @param Wsdl $context
     */
    public function setContext(Wsdl $context);

    /**
     * Create a complex type based on a strategy
     *
     * @param TypeDefinition $type
     * 
     * @return string XSD type
     */
    public function addComplexType(TypeDefinition $type);
}