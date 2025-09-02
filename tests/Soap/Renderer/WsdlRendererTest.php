<?php

namespace Bdf\Soap\Renderer;

use Bdf\Api\Definition\Builder\ComplexTypeDefinitionBuilder;
use Bdf\Api\Definition\Builder\MethodDefinitionBuilder;
use Bdf\Api\Definition\Builder\ServiceDefinitionBuilder;
use Bdf\Api\Definition\ServiceDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Class WsdlRendererTest
 */
class WsdlRendererTest extends TestCase
{
    /**
     * @var WsdlRenderer
     */
    private $renderer;

    /**
     * @var ServiceDefinition
     */
    private $definition;

    /**
     * @var ServiceDefinitionBuilder
     */
    private $builder;

    /**
     *
     */
    protected function setUp(): void
    {
        $this->renderer = new WsdlRenderer();

        $this->builder = new ServiceDefinitionBuilder();

        $this->builder
            ->name('AccountService')
            ->method('foo', function (MethodDefinitionBuilder $builder) {
                $builder->parameter('key', 'string');
                $builder->parameter('bar', 'Bar')->optional();

                $builder->returns('string');
            })
            ->complexType('Bar', function (ComplexTypeDefinitionBuilder $builder) {
                $builder->string('type');
                $builder->integer('count')->optional();
            })
        ;

        $this->definition = $this->builder->build();
    }

    /**
     *
     */
    public function test_render_simple()
    {
        $xml = $this->renderer->render($this->definition, 'http://example.com/soap');

        $this->assertXmlStringEqualsXmlString(<<<XML
<?xml version="1.0"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:tns="http://example.com/soap" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" name="AccountService" targetNamespace="http://example.com/soap">
  <types>
    <xsd:schema targetNamespace="http://example.com/soap">
      <xsd:complexType name="Bar">
        <xsd:all>
          <xsd:element name="type" nillable="true" type="xsd:string"/>
          <xsd:element name="count" nillable="true" type="xsd:int"/>
        </xsd:all>
      </xsd:complexType>
    </xsd:schema>
  </types>
  <portType name="AccountServicePort">
    <operation name="foo">
      <input message="tns:fooIn"/>
      <output message="tns:fooOut"/>
    </operation>
  </portType>
  <binding name="AccountServiceBinding" type="tns:AccountServicePort">
    <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="foo">
      <soap:operation soapAction="http://example.com/soap#foo"/>
      <input>
        <soap:body encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://example.com/soap" use="encoded"/>
      </input>
      <output>
        <soap:body encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://example.com/soap" use="encoded"/>
      </output>
    </operation>
  </binding>
  <service name="AccountServiceService">
    <port binding="tns:AccountServiceBinding" name="AccountServicePort">
      <soap:address location="http://example.com/soap"/>
    </port>
  </service>
  <message name="fooIn">
    <part name="key" type="xsd:string"/>
    <part name="bar" type="tns:Bar"/>
  </message>
  <message name="fooOut">
    <part name="return" type="xsd:string"/>
  </message>
</definitions>
XML
, $xml);
    }


    /**
     *
     */
    public function test_render_with_namespace()
    {
        $this->assertSame($this->builder, $this->builder->namespace('http://ns.example.com/soap'));
        $xml = $this->renderer->render($this->builder->build(), 'http://example.com/soap');

        $this->assertXmlStringEqualsXmlString(<<<XML
<?xml version="1.0"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:tns="http://ns.example.com/soap" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" name="AccountService" targetNamespace="http://ns.example.com/soap">
  <types>
    <xsd:schema targetNamespace="http://ns.example.com/soap">
      <xsd:complexType name="Bar">
        <xsd:all>
          <xsd:element name="type" nillable="true" type="xsd:string"/>
          <xsd:element name="count" nillable="true" type="xsd:int"/>
        </xsd:all>
      </xsd:complexType>
    </xsd:schema>
  </types>
  <portType name="AccountServicePort">
    <operation name="foo">
      <input message="tns:fooIn"/>
      <output message="tns:fooOut"/>
    </operation>
  </portType>
  <binding name="AccountServiceBinding" type="tns:AccountServicePort">
    <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="foo">
      <soap:operation soapAction="http://ns.example.com/soap#foo"/>
      <input>
        <soap:body encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://ns.example.com/soap" use="encoded"/>
      </input>
      <output>
        <soap:body encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" namespace="http://ns.example.com/soap" use="encoded"/>
      </output>
    </operation>
  </binding>
  <service name="AccountServiceService">
    <port binding="tns:AccountServiceBinding" name="AccountServicePort">
      <soap:address location="http://example.com/soap"/>
    </port>
  </service>
  <message name="fooIn">
    <part name="key" type="xsd:string"/>
    <part name="bar" type="tns:Bar"/>
  </message>
  <message name="fooOut">
    <part name="return" type="xsd:string"/>
  </message>
</definitions>
XML
, $xml);
    }

    /**
     *
     */
    public function test_render_document_literal()
    {
        $this->renderer->setDocumentStyle('document-literal');

        $xml = $this->renderer->render($this->definition, 'http://example.com/soap');

        $this->assertXmlStringEqualsXmlString(<<<XML
<?xml version="1.0"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:tns="http://example.com/soap" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" name="AccountService" targetNamespace="http://example.com/soap">
  <types>
    <xsd:schema targetNamespace="http://example.com/soap">
      <xsd:complexType name="Bar">
        <xsd:all>
          <xsd:element name="type" nillable="true" type="xsd:string"/>
          <xsd:element name="count" nillable="true" type="xsd:int"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="foo">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name="key" type="xsd:string"/>
            <xsd:element name="bar" nillable="true" type="tns:Bar"/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
      <xsd:element name="fooResponse">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name="fooResult" type="xsd:string"/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
    </xsd:schema>
  </types>
  <portType name="AccountServicePort">
    <operation name="foo">
      <input message="tns:fooIn"/>
      <output message="tns:fooOut"/>
    </operation>
  </portType>
  <binding name="AccountServiceBinding" type="tns:AccountServicePort">
    <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="foo">
      <soap:operation soapAction="http://example.com/soap#foo"/>
      <input>
        <soap:body use="literal"/>
      </input>
      <output>
        <soap:body use="literal"/>
      </output>
    </operation>
  </binding>
  <service name="AccountServiceService">
    <port binding="tns:AccountServiceBinding" name="AccountServicePort">
      <soap:address location="http://example.com/soap"/>
    </port>
  </service>
  <message name="fooIn">
    <part element="tns:foo" name="parameters"/>
  </message>
  <message name="fooOut">
    <part element="tns:fooResponse" name="parameters"/>
  </message>
</definitions>
XML
, $xml);
    }

    /**
     *
     */
    public function test_render_with_minOccurs()
    {
        $this->renderer
            ->setDocumentStyle('document-literal')
            ->setFlags(['minOccurs' => true])
        ;

        $xml = $this->renderer->render($this->definition, 'http://example.com/soap');

        $this->assertXmlStringEqualsXmlString(<<<XML
<?xml version="1.0"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:tns="http://example.com/soap" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" name="AccountService" targetNamespace="http://example.com/soap">
  <types>
    <xsd:schema targetNamespace="http://example.com/soap">
      <xsd:complexType name="Bar">
        <xsd:all>
          <xsd:element name="type" nillable="true" type="xsd:string"/>
          <xsd:element name="count" minOccurs="0" nillable="true" type="xsd:int"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="foo">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name="key" type="xsd:string"/>
            <xsd:element name="bar" nillable="true" type="tns:Bar" minOccurs="0"/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
      <xsd:element name="fooResponse">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name="fooResult" type="xsd:string"/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
    </xsd:schema>
  </types>
  <portType name="AccountServicePort">
    <operation name="foo">
      <input message="tns:fooIn"/>
      <output message="tns:fooOut"/>
    </operation>
  </portType>
  <binding name="AccountServiceBinding" type="tns:AccountServicePort">
    <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="foo">
      <soap:operation soapAction="http://example.com/soap#foo"/>
      <input>
        <soap:body use="literal"/>
      </input>
      <output>
        <soap:body use="literal"/>
      </output>
    </operation>
  </binding>
  <service name="AccountServiceService">
    <port binding="tns:AccountServiceBinding" name="AccountServicePort">
      <soap:address location="http://example.com/soap"/>
    </port>
  </service>
  <message name="fooIn">
    <part element="tns:foo" name="parameters"/>
  </message>
  <message name="fooOut">
    <part element="tns:fooResponse" name="parameters"/>
  </message>
</definitions>
XML
, $xml);
    }

    /**
     *
     */
    public function test_render_with_minOccurs_without_nillable()
    {
        $this->renderer
            ->setDocumentStyle('document-literal')
            ->setFlags([
                'minOccurs' => true,
                'nillable' => false,
            ])
        ;

        $xml = $this->renderer->render($this->definition, 'http://example.com/soap');

        $this->assertXmlStringEqualsXmlString(<<<XML
<?xml version="1.0"?>
<definitions xmlns="http://schemas.xmlsoap.org/wsdl/" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:soap-enc="http://schemas.xmlsoap.org/soap/encoding/" xmlns:soap12="http://schemas.xmlsoap.org/wsdl/soap12/" xmlns:tns="http://example.com/soap" xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" name="AccountService" targetNamespace="http://example.com/soap">
  <types>
    <xsd:schema targetNamespace="http://example.com/soap">
      <xsd:complexType name="Bar">
        <xsd:all>
          <xsd:element name="type" type="xsd:string"/>
          <xsd:element name="count" minOccurs="0" type="xsd:int"/>
        </xsd:all>
      </xsd:complexType>
      <xsd:element name="foo">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name="key" type="xsd:string"/>
            <xsd:element name="bar" type="tns:Bar" minOccurs="0"/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
      <xsd:element name="fooResponse">
        <xsd:complexType>
          <xsd:sequence>
            <xsd:element name="fooResult" type="xsd:string"/>
          </xsd:sequence>
        </xsd:complexType>
      </xsd:element>
    </xsd:schema>
  </types>
  <portType name="AccountServicePort">
    <operation name="foo">
      <input message="tns:fooIn"/>
      <output message="tns:fooOut"/>
    </operation>
  </portType>
  <binding name="AccountServiceBinding" type="tns:AccountServicePort">
    <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="foo">
      <soap:operation soapAction="http://example.com/soap#foo"/>
      <input>
        <soap:body use="literal"/>
      </input>
      <output>
        <soap:body use="literal"/>
      </output>
    </operation>
  </binding>
  <service name="AccountServiceService">
    <port binding="tns:AccountServiceBinding" name="AccountServicePort">
      <soap:address location="http://example.com/soap"/>
    </port>
  </service>
  <message name="fooIn">
    <part element="tns:foo" name="parameters"/>
  </message>
  <message name="fooOut">
    <part element="tns:fooResponse" name="parameters"/>
  </message>
</definitions>
XML
, $xml);
    }
}
