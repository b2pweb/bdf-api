<?php

namespace Bdf\Soap\Renderer\Strategy;

use Bdf\Api\Definition\ArrayTypeDefinition;
use Bdf\Api\Definition\TypeDefinition;
use Bdf\Soap\Renderer\Wsdl;

/**
 * @package Bdf\Soap\Renderer\Strategy
 */
class ArrayOfTypeComplex extends DefaultComplexType
{
    /**
     * {@inheritDoc}
     */
    public function addComplexType(TypeDefinition $type): string
    {
        if (!($type instanceof ArrayTypeDefinition)) {
            return parent::addComplexType($type);
        }

        if ($type->getInternalType() instanceof ArrayTypeDefinition) {
            throw new \InvalidArgumentException(
                'ArrayOfTypeComplex cannot return nested ArrayOfObject deeper than one level. '
                . 'Use array object properties to return deep nested data.'
            );
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
     * @param TypeDefinition $type
     * @param string         $arrayType
     * @param string         $childType
     */
    protected function addType(TypeDefinition $type, $arrayType, $childType): void
    {
        $this->getContext()->addType($type, $arrayType);

        $dom = $this->getContext()->toDomDocument();

        $complexType = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexType');
        $this->getContext()->getSchema()->appendChild($complexType);

        $complexType->setAttribute('name', substr($arrayType, strpos($arrayType, ':') + 1));

        $complexContent = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexContent');
        $complexType->appendChild($complexContent);

        $xsdRestriction = $dom->createElementNS(Wsdl::XSD_NS_URI, 'restriction');
        $complexContent->appendChild($xsdRestriction);
        $xsdRestriction->setAttribute('base', Wsdl::SOAP_ENC_NS . ':Array');

        $xsdAttribute = $dom->createElementNS(Wsdl::XSD_NS_URI, 'attribute');
        $xsdRestriction->appendChild($xsdAttribute);

        $xsdAttribute->setAttribute('ref', Wsdl::SOAP_ENC_NS . ':arrayType');
        $xsdAttribute->setAttributeNS(
            Wsdl::WSDL_NS_URI,
            'arrayType',
            Wsdl::TYPES_NS . ':' . substr($childType, strpos($childType, ':') + 1) . '[]'
        );
    }
}
