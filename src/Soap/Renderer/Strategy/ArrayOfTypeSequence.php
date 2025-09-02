<?php

namespace Bdf\Soap\Renderer\Strategy;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\TypeDefinition;
use Bdf\Soap\Renderer\Wsdl;

/**
 * @package Bdf\Soap\Renderer\Strategy
 */
class ArrayOfTypeSequence extends DefaultComplexType
{
    /**
     * {@inheritDoc}
     */
    public function addComplexType(TypeDefinition $type): string
    {
        if (!($type instanceof ArrayTypeDefinition)) {
            return parent::addComplexType($type);
        }

        $childType   = $this->getContext()->getType($type->getInternalType());
        $complexType = $this->wrapType($childType);

        $this->addType($type, $complexType, $childType);

        return $complexType;
    }

    /**
     * @param string $childType
     *
     * @return string
     */
    protected function wrapType($childType): string
    {
        return Wsdl::TYPES_NS . ':ArrayOf' . substr($childType, strpos($childType, ':') + 1);
    }

    /**
     * Append the complex type definition to the WSDL via the context access
     *
     * @param TypeDefinition $type
     * @param string         $arrayType
     * @param string         $childType
     */
    protected function addType(TypeDefinition $type, $arrayType, $childType): void
    {
        $this->getContext()->addType($type, $arrayType);

        $dom = $this->getContext()->toDomDocument();

        $arrayTypeName = substr($arrayType, strpos($arrayType, ':') + 1);

        $complexType = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexType');
        $this->getContext()->getSchema()->appendChild($complexType);

        $complexType->setAttribute('name', $arrayTypeName);

        $sequence = $dom->createElementNS(Wsdl::XSD_NS_URI, 'sequence');
        $complexType->appendChild($sequence);

        $element = $dom->createElementNS(Wsdl::XSD_NS_URI, 'element');
        $sequence->appendChild($element);

        $element->setAttribute('name', 'item');
        $element->setAttribute('type', $childType);
        $element->setAttribute('minOccurs', 0);
        $element->setAttribute('maxOccurs', 'unbounded');
    }
}
