<?php

namespace Bdf\Soap\Renderer;

use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\Renderer\RendererInterface;
use Bdf\Api\Definition\ServiceDefinition;
use Bdf\Soap\Renderer\Strategy\ComplexTypeStrategyInterface;
use Bdf\Soap\Renderer\Strategy\ArrayOfTypeComplex;
use Bdf\Soap\Renderer\Strategy\ArrayOfTypeSequence;
use DOMElement;

/**
 * Class WsdlRenderer
 * @package Bdf\Soap\Renderer
 */
class WsdlRenderer implements RendererInterface
{
    /**
     * Permit null value on fields
     * Enabled by default
     */
    const FLAG_NILLABLE = 'nillable';

    /**
     * Permit to set minOccurs=0, which permit to not provide the field, instead of set to null
     * Disabled by default
     */
    const FLAG_MIN_OCCURS = 'minOccurs';

    /**
     * @var array
     */
    protected array $bindingStyles = array(
        'document' => array(
            'style'     => 'document',
            'transport' => 'http://schemas.xmlsoap.org/soap/http'
        ),
        'rpc' => array(
            'style'     => 'rpc',
            'transport' => 'http://schemas.xmlsoap.org/soap/http'
        )
    );

    /**
     * @var array
     */
    protected array $operationBodyStyles = array(
        'encoded' => array(
            'use'           => 'encoded',
            'encodingStyle' => Wsdl::SOAP_ENC_URI
        ),
        'literal' => array(
            'use' => 'literal'
        )
    );

    /**
     * @var string
     */
    protected string $style = 'rpc';

    /**
     * @var string
     */
    protected string $use = 'encoded';

    /**
     * Style flags, for enable, or disable some formats
     *
     * Available flags :
     *
     * - nillable : Permit nillable fields. Enabled by default
     * - minOccurs : Permit to set minOccurs=0, which permit to not provide the field, instead of set to null
     *
     * Note: On C# is a field nillable, whatever the minOccurs attribute, a null field will be set.
     *       For not provide an optional field, nillable must be set to false, and minOccurs to true (also works with PHP)
     *
     * @var bool[]
     */
    private array $flags = [
        self::FLAG_NILLABLE => true
    ];

    /**
     * Set the flags
     * Use WsdlRenderer::FLAG_* constants as key
     *
     * Available flags :
     *
     * - nillable : Permit nillable fields. Enabled by default
     * - minOccurs : Permit to set minOccurs=0, which permit to not provide the field, instead of set to null
     *
     * @param bool[] $flags
     *
     * @return $this
     */
    public function setFlags(array $flags): self
    {
        $this->flags = $flags + $this->flags;

        return $this;
    }

    /**
     * @param string $documentStyle
     *
     * @return self
     *
     * @throws \Exception If document style is invalid
     */
    public function setDocumentStyle(string $documentStyle): self
    {
        switch ($documentStyle) {
            case 'document-literal':
                $this->style = 'document';
                $this->use   = 'literal';
                break;

            case 'rpc-encoded':
                $this->style = 'rpc';
                $this->use   = 'encoded';
                break;

            default:
                throw new \Exception('Invalid document style "' . $documentStyle . '"');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ServiceDefinition $definition, $uri): string
    {
        $namespace = $definition->getNamespace() ?? $uri;
        $wsdl = new Wsdl($definition->getName(), $namespace, $this->instanciateStrategy());

        $wsdl->addSchemaTypeSection();

        $port = $wsdl->addPortType($definition->getName() . 'Port');
        $binding = $wsdl->addBinding($definition->getName() . 'Binding', Wsdl::TYPES_NS . ':' . $definition->getName() . 'Port');

        $wsdl->addSoapBinding($binding, $this->bindingStyles[$this->style]['style'], $this->bindingStyles[$this->style]['transport']);

        $wsdl->addService($definition->getName() . 'Service', $definition->getName() . 'Port', Wsdl::TYPES_NS . ':' . $definition->getName() . 'Binding', $uri);

        foreach ($definition->getMethods() as $method) {
            $this->addFunctionToWsdl($method, $wsdl, $port, $binding, $namespace);
        }

        $xml = $wsdl->toXml();

        // Remove useless namespace if generated
        // for ensure that generates WSDL remains the same
        if (str_contains($xml, '<'.Wsdl::WSDL_NS.':types>')) {
            $xml = strtr($xml, [
                '<'.Wsdl::WSDL_NS.':' => '<',
                '</'.Wsdl::WSDL_NS.':' => '</',
            ]);
        }

        return $xml;
    }

    /**
     * @return ComplexTypeStrategyInterface
     *
     * @throws \Exception If encoding is invalid
     */
    protected function instanciateStrategy(): ComplexTypeStrategyInterface
    {
        switch ($this->use) {
            case 'encoded':
                return new ArrayOfTypeComplex($this->flags);

            case 'literal':
                return new ArrayOfTypeSequence($this->flags);

            default:
                throw new \Exception('No wsdl strategy associated to "' . $this->use .'"');
        }
    }

    /**
     * @param MethodDefinition $method
     * @param Wsdl             $wsdl
     * @param DOMElement       $port
     * @param DOMElement       $binding
     * @param string           $uri The function name. Should be in form : [namespace]#[name]
     */
    protected function addFunctionToWsdl(MethodDefinition $method, Wsdl $wsdl, DOMElement $port, DOMElement $binding, $uri): void
    {
        $args = array();

        if ($this->style === 'document') {
            $sequence = array();

            foreach ($method->getParameters() as $parameter) {
                $sequenceElement = array(
                    'name' => $parameter->getName(),
                    'type' => $wsdl->getType($parameter->getType())
                );

                if ($parameter->isOptional()) {
                    if (!empty($this->flags[self::FLAG_NILLABLE])) {
                        $sequenceElement['nillable'] = 'true';
                    }

                    if (!empty($this->flags[self::FLAG_MIN_OCCURS])) {
                        $sequenceElement['minOccurs'] = '0';
                    }
                }

                $sequence[] = $sequenceElement;
            }

            $args['parameters'] = array(
                'element' => $wsdl->addElement(array(
                    'name'     => $method->getName(),
                    'sequence' => $sequence
                ))
            );
        } else {
            foreach ($method->getParameters() as $parameter) {
                $args[$parameter->getName()] = array(
                    'type' => $wsdl->getType($parameter->getType())
                );
            }
        }

        $wsdl->addMessage($method->getName() . 'In', $args);

        $isOneWayMessage = (!$method->getReturn() && !$method->isThrowingException());

        if (!$isOneWayMessage) {
            $args = array();

            if ($this->style === 'document') {
                $sequence = array();

                if ($method->getReturn()) {
                    $sequence[] = array(
                        'name' => $method->getName() . 'Result',
                        'type' => $wsdl->getType($method->getReturn())
                    );
                }

                $args['parameters'] = array(
                    'element' => $wsdl->addElement(array(
                        'name'     => $method->getName() . 'Response',
                        'sequence' => $sequence
                    ))
                );
            } elseif ($method->getReturn()) {
                $args['return'] = array(
                    'type' => $wsdl->getType($method->getReturn())
                );

                /*
                
                if ($this->faultType) {
                    $args['fault'] = array('type' => $wsdl->getType($this->faultType));
                }

                */
            }

            $wsdl->addMessage($method->getName() . 'Out', $args);
        }

        if ($isOneWayMessage) {
            $portOperation = $wsdl->addPortOperation(
                $port,
                $method->getName(),
                Wsdl::TYPES_NS . ':' . $method->getName() . 'In', false
            );
        } else {
            $portOperation = $wsdl->addPortOperation(
                $port,
                $method->getName(),
                Wsdl::TYPES_NS . ':' . $method->getName() . 'In', Wsdl::TYPES_NS . ':' . $method->getName() . 'Out'
            );
        }

        
        
        // if ($this->getAddDescriptionTag()) {
//        $desc = $this->discoveryStrategy->getFunctionDocumentation($function);
            if ($method->getDescription()) {
                $wsdl->addDocumentation($portOperation, $method->getDescription());
            }
        // }

         

        $operationBodyStyle = $this->operationBodyStyles[$this->use];

        if ($this->style === 'rpc' && empty($operationBodyStyle['namespace'])) {
            $operationBodyStyle['namespace'] = '' . $uri;
        }

        if ($isOneWayMessage) {
            $operation = $wsdl->addBindingOperation($binding, $method->getName(), $operationBodyStyle);
        } else {
            $operation = $wsdl->addBindingOperation($binding, $method->getName(), $operationBodyStyle, $operationBodyStyle);
        }

        $wsdl->addSoapOperation($operation, $uri . '#' . $method->getName());
    }
}
