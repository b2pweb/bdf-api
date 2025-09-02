<?php

namespace Bdf\Soap\Renderer;

use Bdf\Api\Definition\TypeDefinition;
use Bdf\Soap\Renderer\Strategy\ComplexTypeStrategyInterface;
use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMNode;
use DOMXPath;

/**
 * @package Bdf\Soap\Renderer
 */
class Wsdl
{
    /**#@+
     * XML Namespace uris and prefixes.
     */
    const XML_NS            = 'xmlns';
    const XML_NS_URI        = 'http://www.w3.org/2000/xmlns/';
    const WSDL_NS           = 'wsdl';
    const WSDL_NS_URI       = 'http://schemas.xmlsoap.org/wsdl/';
    const SOAP_11_NS        = 'soap';
    const SOAP_11_NS_URI    = 'http://schemas.xmlsoap.org/wsdl/soap/';
    const SOAP_12_NS        = 'soap12';
    const SOAP_12_NS_URI    = 'http://schemas.xmlsoap.org/wsdl/soap12/';
    const SOAP_ENC_NS       = 'soap-enc';
    const SOAP_ENC_URI      = 'http://schemas.xmlsoap.org/soap/encoding/';
    const XSD_NS            = 'xsd';
    const XSD_NS_URI        = 'http://www.w3.org/2001/XMLSchema';
    const TYPES_NS          = 'tns';
    /**#@-*/

    /**
     * @var array Map of PHP Class names to WSDL QNames.
     */
    protected array $classmap = array();

    /**
     * @var DOMDocument DOM Instance
     */
    protected DOMDocument $dom;

    /**
     * @var array<string, string> Types defined on schema
     */
    protected array $includedTypes = [];

    /**
     * @var DOMElement|null
     */
    protected ?DOMElement $schema = null;

    /**
     * Strategy for detection of complex types
     */
    protected ComplexTypeStrategyInterface $strategy;

    /**
     * @var string URI where the WSDL will be available
     */
    protected string $uri;

    /**
     * @var DOMElement WSDL Root XML_Tree_Node
     */
    protected DOMElement $wsdl;

    /**
     * @param  string  $name Name of the Web Service being Described
     * @param  string $uri URI namespace. If a namespace is not provided, use the API URL
     * @param  null|ComplexTypeStrategyInterface $strategy Strategy for detection of complex types
     * @param  array $classmap Map of PHP Class names to WSDL QNames
     * @throws \RuntimeException
     */
    public function __construct($name, string $uri, ?ComplexTypeStrategyInterface $strategy = null, array $classmap = [])
    {
        $this->setUri($uri);

        $this->classmap = $classmap;
        $this->dom      = $this->getDOMDocument($name, $this->getUri());
        $this->wsdl     = $this->dom->documentElement;

        $this->setComplexTypeStrategy($strategy ?: new Strategy\DefaultComplexType);
    }

    /**
     * Get the wsdl XML document with all namespaces and required attributes
     *
     * @param  string $uri
     * @param  string $name
     * @return DOMDocument
     */
    protected function getDOMDocument($name, string $uri): DOMDocument
    {
        $dom = new DOMDocument();

        // @todo new option for debug mode ?
        $dom->preserveWhiteSpace    = false;
        $dom->formatOutput          = false;
        $dom->resolveExternals      = false;
        $dom->encoding              = 'UTF-8';
        $dom->substituteEntities    = false;

        $definitions = $dom->createElementNS(self::WSDL_NS_URI, 'definitions');
        $dom->appendChild($definitions);

        $uri = $this->sanitizeUri($uri);
        $this->setAttributeWithSanitization($definitions, 'name',               $name);
        $this->setAttributeWithSanitization($definitions, 'targetNamespace',    $uri);

        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::WSDL_NS,      self::WSDL_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::TYPES_NS,     $uri);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::SOAP_11_NS,   self::SOAP_11_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::XSD_NS,       self::XSD_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::SOAP_ENC_NS,  self::SOAP_ENC_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::SOAP_12_NS,   self::SOAP_12_NS_URI);

        return $dom;
    }

    /**
     * Retrieve target namespace of the WSDL document.
     *
     * @return string
     */
    public function getTargetNamespace()
    {
        $targetNamespace = null;

        if ($this->wsdl !== null) {
            $targetNamespace = $this->wsdl->getAttribute('targetNamespace');
        }

        return $targetNamespace;
    }

    /**
     * Get the class map of php to wsdl mappings..
     *
     * @return array
     */
    public function getClassmap(): array
    {
        return $this->classmap;
    }

    /**
     * Set the class map of php to wsdl mappings..
     *
     * @return self
     */
    public function setClassmap(array $classmap): self
    {
        $this->classmap = $classmap;

        return $this;
    }

    /**
     * Set a new uri for this WSDL
     *
     * @param  string $uri
     * @return self
     */
    public function setUri(string $uri): self
    {
        $uri = $this->sanitizeUri($uri);

        $oldUri = $this->uri ?? null; // This method is called by the constructor, so the property uri is not yet initialized
        $this->uri = $uri;

        // setUri is called by the constructor, before initializing the DOM property
        // So we need to check if the DOM is already initialized
        if (isset($this->dom)) {
            // namespace declarations are NOT true attributes so one must explicitly set on root element
            //                                                                  xmlns:tns = $uri
            $this->dom->documentElement->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::TYPES_NS, $uri);

            $xpath = new DOMXPath($this->dom);
            $xpath->registerNamespace('default',            self::WSDL_NS_URI);

            $xpath->registerNamespace(self::TYPES_NS,       $uri);
            $xpath->registerNamespace(self::SOAP_11_NS,     self::SOAP_11_NS_URI);
            $xpath->registerNamespace(self::SOAP_12_NS,     self::SOAP_12_NS_URI);
            $xpath->registerNamespace(self::XSD_NS,         self::XSD_NS_URI);
            $xpath->registerNamespace(self::SOAP_ENC_NS,    self::SOAP_ENC_URI);
            $xpath->registerNamespace(self::WSDL_NS,        self::WSDL_NS_URI);

            // Select only attribute nodes. Data nodes does not contain uri
            // except for documentation node but this is for the user to decide.
            // This list does not include xmlns:tsn attribute of document root.
            // That attribute is changed above.
            $attributeNodes = $xpath->query('//attribute::*[contains(., "' . $oldUri . '")]');

            foreach ($attributeNodes as $node) {
                $attributeValue = $this->dom->createTextNode(str_replace($oldUri, $uri, $node->nodeValue));
                $node->replaceChild($attributeValue, $node->childNodes->item(0));
            }
        }

        return $this;
    }

    /**
     * Return WSDL uri
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Function for sanitizing uri
     *
     * @param  string $uri
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function sanitizeUri(string $uri): string
    {
        $uri = trim($uri);
        $uri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8', false);

        if (empty($uri)) {
            throw new \InvalidArgumentException('Uri contains invalid characters or is empty');
        }

        return $uri;
    }

    /**
     * Set a strategy for complex type detection and handling
     *
     * @param  ComplexTypeStrategyInterface $strategy
     *
     * @return $this
     */
    public function setComplexTypeStrategy(ComplexTypeStrategyInterface $strategy): self
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Get the current complex type strategy
     *
     * @return ComplexTypeStrategyInterface
     */
    public function getComplexTypeStrategy(): ComplexTypeStrategyInterface
    {
        return $this->strategy;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_messages message} element to the WSDL
     *
     * @param  string $messageName Name for the {@link http://www.w3.org/TR/wsdl#_messages message}
     * @param  array $parts An array of {@link http://www.w3.org/TR/wsdl#_message parts}
     *                      The array is constructed like:
     *                          'name of part' => 'part xml schema data type' or
     *                          'name of part' => array('type' => 'part xml schema type')  or
     *                          'name of part' => array('element' => 'part xml element name')
     * @return DOMElement The new message's XML_Tree_Node for use in {@link function addDocumentation}
     */
    public function addMessage(string $messageName, array $parts): DOMElement
    {
        $message = $this->dom->createElementNS(self::WSDL_NS_URI, 'message');
        $message->setAttribute('name', $messageName);

        if (count($parts) > 0) {
            foreach ($parts as $name => $type) {
                $part = $this->dom->createElementNS(self::WSDL_NS_URI, 'part');
                $message->appendChild($part);

                $part->setAttribute('name', $name);
                if (is_array($type)) {
                    $this->arrayToAttributes($part, $type);
                } else {
                    $this->setAttributeWithSanitization($part, 'type', $type);
                }
            }
        }

        $this->wsdl->appendChild($message);
        return $message;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_porttypes portType} element to the WSDL
     *
     * @param  string $name portType element's name
     * @return DOMElement The new portType's XML_Tree_Node for use in {@link function addPortOperation} and <a href='psi_element://function'>addDocumentation</a>@link function addDocumentation}
     */
    public function addPortType(string $name): DOMElement
    {
        $portType = $this->dom->createElementNS(self::WSDL_NS_URI, 'portType');
        $this->wsdl->appendChild($portType);
        $portType->setAttribute('name', $name);
        return $portType;
    }

    /**
     * Add an {@link http://www.w3.org/TR/wsdl#request-response operation} element to a portType element
     *
     * @param  DOMElement $portType a portType XML_Tree_Node, from {@link function addPortType}
     * @param  string      $name     Operation name
     * @param  bool|string $input    Input Message
     * @param  bool|string $output   Output Message
     * @param  bool|string $fault    Fault Message
     * @return DOMElement The new operation's XML_Tree_Node for use in {@link function addDocumentation}
     */
    public function addPortOperation(DOMElement $portType, string $name, string|bool $input = false, string|bool $output = false, string|bool $fault = false): DOMElement
    {
        $operation = $this->dom->createElementNS(self::WSDL_NS_URI, 'operation');
        $portType->appendChild($operation);

        $operation->setAttribute('name', $name);

        if (is_string($input) && (strlen(trim($input)) >= 1)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'input');
            $operation->appendChild($node);
            $node->setAttribute('message', $input);
        }

        if (is_string($output) && (strlen(trim($output)) >= 1)) {
            $node= $this->dom->createElementNS(self::WSDL_NS_URI, 'output');
            $operation->appendChild($node);
            $node->setAttribute('message', $output);
        }

        if (is_string($fault) && (strlen(trim($fault)) >= 1)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'fault');
            $operation->appendChild($node);
            $node->setAttribute('message', $fault);
        }

        return $operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_bindings binding} element to WSDL
     *
     * @param  string $name Name of the Binding
     * @param  string $portType name of the portType to bind
     * @return DOMElement The new binding's XML_Tree_Node for use with {@link function addBindingOperation} and {@link function addDocumentation}
     */
    public function addBinding(string $name, string $portType): DOMElement
    {
        $binding = $this->dom->createElementNS(self::WSDL_NS_URI, 'binding');
        $this->wsdl->appendChild($binding);

        $this->setAttribute($binding, 'name', $name);
        $this->setAttribute($binding, 'type', $portType);

        return $binding;
    }

    /**
     * Add an operation to a binding element
     *
     * @param  DOMElement $binding A binding XML_Tree_Node returned by {@link function addBinding}
     * @param  string $name
     * @param  array|bool $input  An array of attributes for the input element,
     *                            allowed keys are: 'use', 'namespace', 'encodingStyle'.
     *                            {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param  array|bool $output An array of attributes for the output element,
     *                            allowed keys are: 'use', 'namespace', 'encodingStyle'.
     *                            {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param  array|bool $fault  An array with attributes for the fault element,
     *                            allowed keys are: 'name', 'use', 'namespace', 'encodingStyle'.
     *                            {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param  int $soapVersion SOAP version: SOAP_1_1 or SOAP_1_2, default: SOAP_1_1
     * @return DOMElement The new Operation's XML_Tree_Node for use with {@link function addSoapOperation} and {@link function addDocumentation}
     */
    public function addBindingOperation(DOMElement $binding, string $name, array|bool $input = false, array|bool $output = false, $fault = false, $soapVersion = SOAP_1_1): DOMElement
    {
        $operation = $this->dom->createElementNS(self::WSDL_NS_URI, 'operation');
        $binding->appendChild($operation);

        $this->setAttribute($operation, 'name', $name);

        if (is_array($input) && !empty($input)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'input');
            $operation->appendChild($node);

            $soapNode = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'body');
            $node->appendChild($soapNode);

            $this->arrayToAttributes($soapNode, $input);
        }

        if (is_array($output) && !empty($output)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'output');
            $operation->appendChild($node);

            $soapNode = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'body');
            $node->appendChild($soapNode);

            $this->arrayToAttributes($soapNode, $output);
        }

        if (is_array($fault) && !empty($fault)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'fault');
            $operation->appendChild($node);

            $this->arrayToAttributes($node, $fault);
        }

        return $operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:binding SOAP binding} element to a Binding element
     *
     * @param  DOMElement $binding A binding XML_Tree_Node returned by {@link function addBinding}
     * @param  string $style binding style, possible values are "rpc" (the default) and "document"
     * @param  string $transport Transport method (defaults to HTTP)
     * @param  int $soapVersion SOAP version: SOAP_1_1 or SOAP_1_2, default: SOAP_1_1
     * @return DOMElement
     */
    public function addSoapBinding(DOMElement $binding, string $style = 'document', string $transport = 'http://schemas.xmlsoap.org/soap/http', $soapVersion = SOAP_1_1): DOMElement
    {
        $soapBinding = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'binding');
        $binding->appendChild($soapBinding);

        $soapBinding->setAttribute('style', $style);
        $soapBinding->setAttribute('transport', $transport);

        return $soapBinding;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:operation SOAP operation} to an operation element
     *
     * @param  DOMElement $operation An operation XML_Tree_Node returned by {@link function addBindingOperation}
     * @param  string $soapAction SOAP Action
     * @param  int $soapVersion SOAP version: SOAP_1_1 or SOAP_1_2, default: SOAP_1_1
     * @return DOMElement
     */
    public function addSoapOperation(DOMElement $operation, string $soapAction, $soapVersion = SOAP_1_1): DOMElement
    {
        $soapOperation = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'operation');
        $operation->insertBefore($soapOperation, $operation->firstChild);

        $this->setAttributeWithSanitization($soapOperation, 'soapAction', $soapAction);

        return $soapOperation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_services service} element to the WSDL
     *
     * @param  string $name Service Name
     * @param  string $portName Name of the port for the service
     * @param  string $binding Binding for the port
     * @param  string $location SOAP Address for the service. Should be the endpoint, not the namespace
     * @param  int $soapVersion SOAP version: SOAP_1_1 or SOAP_1_2, default: SOAP_1_1
     * @return DOMElement The new service's XML_Tree_Node for use with {@link function addDocumentation}
     */
    public function addService(string $name, string $portName, string $binding, string $location, $soapVersion = SOAP_1_1): DOMElement
    {
        $service = $this->dom->createElementNS(self::WSDL_NS_URI, 'service');
        $this->wsdl->appendChild($service);

        $service->setAttribute('name', $name);


        $port = $this->dom->createElementNS(self::WSDL_NS_URI, 'port');
        $service->appendChild($port);

        $port->setAttribute('name', $portName);
        $port->setAttribute('binding', $binding);

        $soapAddress = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'address');
        $port->appendChild($soapAddress);

        $this->setAttributeWithSanitization($soapAddress, 'location', $location);
        return $service;
    }

    /**
     * Add a documentation element to any element in the WSDL.
     *
     * Note that the WSDL {@link http://www.w3.org/TR/wsdl#_documentation specification} uses 'document',
     * but the WSDL {@link http://schemas.xmlsoap.org/wsdl/ schema} uses 'documentation' instead.
     * The {@link http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html#WSDL_documentation_Element WS-I Basic Profile 1.1} recommends using 'documentation'.
     *
     * @param  DOMElement $inputNode An XML_Tree_Node returned by another method to add the documentation to
     * @param  string $documentation Human readable documentation for the node
     * @return DOMElement The documentation element
     */
    public function addDocumentation(DOMElement $inputNode, string $documentation): DOMElement
    {
        if ($inputNode === $this) { // @fixme wtf ?
            $node = $this->dom->documentElement;
        } else {
            $node = $inputNode;
        }

        $doc = $this->dom->createElementNS(self::WSDL_NS_URI, 'documentation');
        if ($node->hasChildNodes()) {
            $node->insertBefore($doc, $node->firstChild);
        } else {
            $node->appendChild($doc);
        }

        $docCData = $this->dom->createTextNode(str_replace(array("\r\n", "\r"), "\n", $documentation));
        $doc->appendChild($docCData);
        return $doc;
    }

    /**
     * Add WSDL Types element
     *
     * @param  DOMDocument|DOMNode|DOMElement|DOMDocumentFragment $types A DOMDocument|DOMNode|DOMElement|DOMDocumentFragment with all the XML Schema types defined in it
     */
    public function addTypes(DOMNode $types): void
    {
        if ($types instanceof DOMDocument) {
            $dom = $this->dom->importNode($types->documentElement);
            $this->wsdl->appendChild($dom);
        } elseif ($types instanceof DOMNode || $types instanceof DOMElement || $types instanceof DOMDocumentFragment ) {
            $dom = $this->dom->importNode($types);
            $this->wsdl->appendChild($dom);
        }
    }

    /**
     * Add a complex type name that is part of this WSDL and can be used in signatures.
     *
     * @param  TypeDefinition $type
     * @param  string $wsdlType
     * @return self
     */
    public function addType(TypeDefinition $type, string $wsdlType): self
    {
        if (!isset($this->includedTypes[$type->getName()])) {
            $this->includedTypes[$type->getName()] = $wsdlType;
        }
        
        return $this;
    }

    /**
     * Return an array of all currently included complex types
     *
     * @return array<string, string>
     */
    public function getTypes(): array
    {
        return $this->includedTypes;
    }

    /**
     * Return the Schema node of the WSDL
     *
     * @return DOMElement
     */
    public function getSchema(): DOMElement
    {
        if ($this->schema == null) {
            $this->addSchemaTypeSection();
        }
        return $this->schema;
    }

    /**
     * Return the WSDL as XML
     *
     * @return string WSDL as XML
     */
    public function toXML(): string
    {
        $this->dom->normalizeDocument();
        return $this->dom->saveXML();
    }

    /**
     * Return DOM Document
     *
     * @return DOMDocument
     */
    public function toDomDocument(): DOMDocument
    {
        $this->dom->normalizeDocument();
        return $this->dom;
    }

    /**
     * Echo the WSDL as XML
     *
     * @param  string|false|null $filename
     * @return bool
     */
    public function dump(string|false|null $filename = false): bool
    {
        $this->dom->normalizeDocument();

        if (!$filename) {
            echo $this->toXML();
            return true;
        }

        return (bool) file_put_contents($filename, $this->toXML());
    }

    /**
     * Returns an XSD Type for the given PHP type
     *
     * @param TypeDefinition $type PHP Type to get the XSD type for
     * 
     * @return string
     */
    public function getType(TypeDefinition $type): string
    {
        switch (strtolower($type->getName())) {
            case 'string':
            case 'str':
                return self::XSD_NS . ':string';

            case 'long':
                return self::XSD_NS . ':long';

            case 'int':
            case 'integer':
                return self::XSD_NS . ':int';

            case 'float':
                return self::XSD_NS . ':float';

            case 'double':
                return self::XSD_NS . ':double';

            case 'boolean':
            case 'bool':
                return self::XSD_NS . ':boolean';

            case 'array':
                return self::SOAP_ENC_NS . ':Array';

            case 'object':
                return self::XSD_NS . ':struct';

            case 'mixed':
                return self::XSD_NS . ':anyType';

            case 'datetime':
                return self::XSD_NS . ':dateTime';
            
            case 'void':
                return '';

            default:
                return $this->addComplexType($type);
        }
    }

    /**
     * This function makes sure a complex types section and schema additions are set.
     *
     * @return self
     */
    public function addSchemaTypeSection(): self
    {
        if ($this->schema === null) {
            $types = $this->dom->createElementNS(self::WSDL_NS_URI, 'types');
            $this->wsdl->appendChild($types);

            $this->schema = $this->dom->createElementNS(self::XSD_NS_URI, 'schema');
            $types->appendChild($this->schema);

            $this->setAttributeWithSanitization($this->schema, 'targetNamespace', $this->getUri());
        }

        return $this;
    }

    /**
     * Translate mapped php type into WSDL QName
     *
     * @param  string $type
     * @return string QName
     */
    public function translateType(string $type): string
    {
        if (isset($this->classmap[$type])) {
            return $this->classmap[$type];
        }

        return $type;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_types types} data type definition
     *
     * @param  TypeDefinition $type Name of the class to be specified
     * @return string XSD Type for the given PHP type
     */
    public function addComplexType(TypeDefinition $type): string
    {
        if (isset($this->includedTypes[$type->getName()])) {
            return $this->includedTypes[$type->getName()];
        }

        $this->addSchemaTypeSection();

        $strategy = $this->getComplexTypeStrategy();
        
        $strategy->setContext($this);

        // delegates the detection of a complex type to the current strategy
        return $strategy->addComplexType($type);
    }

    /**
     * Parse an xsd:element represented as an array into a DOMElement.
     *
     * @param  array $element an xsd:element represented as an array
     * @return DOMElement parsed element
     * @throws \RuntimeException if $element is not an array
     */
    protected function _parseElement(array $element): DOMElement
    {
        $elementXML = $this->dom->createElementNS(self::XSD_NS_URI, 'element');
        foreach ($element as $key => $value) {
            if (in_array($key, array('sequence', 'all', 'choice'))) {
                if (is_array($value)) {
                    $complexType = $this->dom->createElementNS(self::XSD_NS_URI, 'complexType');
                    if (count($value) > 0) {
                        $container = $this->dom->createElementNS(self::XSD_NS_URI, $key);
                        foreach ($value as $subElement) {
                            $subElementXML = $this->_parseElement($subElement);
                            $container->appendChild($subElementXML);
                        }
                        $complexType->appendChild($container);
                    }
                    $elementXML->appendChild($complexType);
                }
            } else {
                $elementXML->setAttribute($key, $value);
            }
        }

        return $elementXML;
    }

    /**
     * Prepare attribute value for specific attributes
     *
     * @param  string $name
     * @param  mixed $value
     * @return mixed safe value or original $value
     */
    protected function sanitizeAttributeValueByName(string $name, mixed $value): mixed
    {
        switch (strtolower($name)) {
            case 'targetnamespace':
            case 'encodingstyle':
            case 'soapaction':
            case 'location':
                return $this->sanitizeUri((string) $value);

            default:
                return $value;
        }
    }

    /**
     * Convert associative array to attributes of given node using optional {@link function sanitizeAttributeValueByName}
     *
     * @param  DOMNode $node
     * @param  array $attributes
     * @param  bool $withSanitizer
     */
    protected function arrayToAttributes(\DOMNode $node, array $attributes, bool $withSanitizer = true): void
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            if ($withSanitizer) {
                $this->setAttributeWithSanitization($node, $attributeName, $attributeValue);
            } else {
                $this->setAttribute($node, $attributeName, $attributeValue);
            }
        }
    }

    /**
     * Set attribute to given node using {@link function sanitizeAttributeValueByName}
     *
     * @param  DOMNode $node
     * @param  string $attributeName
     * @param  mixed $attributeValue
     */
    protected function setAttributeWithSanitization(\DOMNode $node, string $attributeName, $attributeValue): void
    {
        $attributeValue = $this->sanitizeAttributeValueByName($attributeName, $attributeValue);
        $this->setAttribute($node, $attributeName, $attributeValue);
    }

    /**
     * Set attribute to given node
     *
     * @param  DOMNode $node
     * @param  string $attributeName
     * @param  mixed $attributeValue
     */
    protected function setAttribute(\DOMNode $node, string $attributeName, $attributeValue): void
    {
        $attributeNode = $node->ownerDocument->createAttribute($attributeName);
        $node->appendChild($attributeNode);

        $attributeNodeValue = $node->ownerDocument->createTextNode($attributeValue);
        $attributeNode->appendChild($attributeNodeValue);
    }

    /**
     * Return soap namespace uri according to $soapVersion
     *
     * @param  int $soapVersion SOAP_1_1 or SOAP_1_2 constants
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getSoapNamespaceUriByVersion($soapVersion): string
    {
        if ($soapVersion != SOAP_1_1 && $soapVersion != SOAP_1_2) {
            throw new \InvalidArgumentException('Invalid SOAP version, use constants: SOAP_1_1 or SOAP_1_2');
        }

        if ($soapVersion == SOAP_1_1) {
            return self::SOAP_11_NS_URI;
        }

        return self::SOAP_12_NS_URI;
    }

    /**
     * Add an xsd:element represented as an array to the schema.
     *
     * Array keys represent attribute names and values their respective value.
     * The 'sequence', 'all' and 'choice' keys must have an array of elements as their value,
     * to add them to a nested complexType.
     *
     * Example: array( 'name' => 'MyElement',
     *                 'sequence' => array( array('name' => 'myString', 'type' => 'string'),
     *                                      array('name' => 'myInteger', 'type' => 'int') ) );
     * Resulting XML: <xsd:element name="MyElement"><xsd:complexType><xsd:sequence>
     *                  <xsd:element name="myString" type="string"/>
     *                  <xsd:element name="myInteger" type="int"/>
     *                </xsd:sequence></xsd:complexType></xsd:element>
     *
     * @param  array $element an xsd:element represented as an array
     * @return string xsd:element for the given element array
     */
    public function addElement(array $element): string
    {
        $schema = $this->getSchema();
        $elementXml = $this->_parseElement($element);
        $schema->appendChild($elementXml);

        return self::TYPES_NS . ':' . $element['name'];
    }
}
