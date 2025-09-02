<?php

namespace Bdf\Soap\Renderer\Strategy;

use Bdf\Api\Definition\ComplexTypeDefinition;
use Bdf\Api\Definition\TypeDefinition;
use Bdf\Soap\Renderer\Wsdl;
use Bdf\Soap\Renderer\WsdlRenderer;

/**
 * @package Bdf\Soap\Renderer\Strategy
 */
class DefaultComplexType extends AbstractComplexTypeStrategy
{
    /**
     * @var array
     */
    private array $flags;

    /**
     * DefaultComplexType constructor.
     *
     * @param array $flags
     */
    public function __construct(array $flags = [])
    {
        $this->flags = $flags;
    }

    /**
     * Check the flag value
     *
     * @param string $name
     *
     * @return bool
     *
     * @see WsdlRenderer::setFlags()
     */
    public function flag(string $name): bool
    {
        return !empty($this->flags[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function addComplexType(TypeDefinition $type): string
    {
        if (!($type instanceof ComplexTypeDefinition)) {
            return '';
        }

        $soapType = Wsdl::TYPES_NS . ':' . $type->getName();

        $this->getContext()->addType($type, $soapType);

        $dom = $this->getContext()->toDomDocument();

        $all = $dom->createElementNS(Wsdl::XSD_NS_URI, 'all');

        $complexType = $dom->createElementNS(Wsdl::XSD_NS_URI, 'complexType');

        $complexType->setAttribute('name', $type->getName());

        foreach ($type->getProperties() as $property) {
            $element = $dom->createElementNS(Wsdl::XSD_NS_URI, 'element');

            $element->setAttribute('name', $property->getName());
            $element->setAttribute('type', $this->getContext()->getType($property->getType()));

            if ($property->isNillable() && $this->flag(WsdlRenderer::FLAG_NILLABLE)) {
                $element->setAttribute('nillable', 'true');
            }

            if ($property->isOptional() && $this->flag(WsdlRenderer::FLAG_MIN_OCCURS)) {
                $element->setAttribute('minOccurs', '0');
            }

            $all->appendChild($element);
        }

        $complexType->appendChild($all);

        $this->getContext()->getSchema()->appendChild($complexType);

        return $soapType;
    }
}
