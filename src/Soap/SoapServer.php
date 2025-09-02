<?php

namespace Bdf\Soap;

use Bdf\Api\Server\ApiServer;
use Bdf\Soap\Server\Request;
use Bdf\Soap\Server\Response;
use Bdf\Soap\Wrapper\DefaultWrapper;
use Bdf\Soap\Wrapper\DocumentLiteralWrapper;
use Closure;

/**
 * @package Bdf\Soap
 */
class SoapServer extends ApiServer
{
    protected ?\SoapServer $soapServer = null;

    /**
     * @fixme is it really possible to have a null wsdl?
     */
    protected string|Closure|null $wsdl = null;

    /**
     * @var array
     */
    private array $soapOptionKeys = array(
        'actor',
        'cache_wsdl',
        'classmap',
        'encoding',
        'features',
        'send_errors',
        'soap_version',
        'typemap',
        'uri',
    );

    /**
     * {@inheritdoc}
     *
     * @fixme: cannot add type: it's an overridden property
     */
    protected $options = array(
        'document'      => 'rpc-encoded', //document-literal | rpc-encoded
        'features'      => SOAP_SINGLE_ELEMENT_ARRAYS,
        'soap_version'  => SOAP_1_2,
        'class'         => null,
        'functions'     => null,
        'object'        => null,
        'persistence'   => null,
    );

    /**
     * @var array<string, class-string<DefaultWrapper>>
     */
    protected array $wrapperClasses = [
        'document-literal' => DocumentLiteralWrapper::class,
        'rpc-encoded'      => DefaultWrapper::class,
    ];

    /**
     * {@inheritdoc}
     * @fixme: cannot add type: it's an overridden property
     */
    protected $mappingCodes = array(
        400 => 'Sender',
        403 => 'Sender',
        404 => 'Sender',
    );

    /**
     * @param string|(Closure(SoapServer):string)|null $wsdl
     * @param array  $options
     */
    public function __construct(string|Closure|null $wsdl = null, array $options = [])
    {
        parent::__construct(['wsdl' => $wsdl] + $options);

        $this->setRequest(new Request());
        $this->setResponse(new Response());
    }

    /**
     * @param string|(Closure(SoapServer):string)|null $wsdl
     * @return void
     */
    public function setWsdl(string|Closure|null $wsdl): void
    {
        $this->wsdl = $wsdl;
    }

    public function getWsdl(): ?string
    {
        if ($this->wsdl instanceof Closure) {
            $this->wsdl = ($this->wsdl)($this);
        }

        return $this->wsdl;
    }

    /**
     * @return array
     */
    public function getSoapOptions(): array
    {
        $soapOptions = [];

        foreach ($this->soapOptionKeys as $soapOption) {
            if ($this->getOption($soapOption)) {
                $soapOptions[$soapOption] = $this->getOption($soapOption);
            }
        }

        return $soapOptions;
    }

    /**
     * @return \SoapServer
     */
    public function getSoapServer(): \SoapServer
    {
        if (!$this->soapServer) {
            $this->loadSoapServer();
        }

        return $this->soapServer;
    }

    /**
     * {@inheritdoc}
     *
     * @return \SoapFault
     */
    protected function instanciateFault($fault = null, $code = null, $data = null)
    {
        $allowedFaultModes = array(
            'Client',               // SOAP 1.1
            'Server',               // SOAP 1.1
            'VersionMismatch',      // SOAP 1.1, SOAP 1.2
            'MustUnderstand',       // SOAP 1.1, SOAP 1.2
            'DataEncodingUnknown',  // SOAP 1.2
            'Sender',               // SOAP 1.2
            'Receiver',             // SOAP 1.2
        );

        if (!in_array($code, $allowedFaultModes, true)) {
            $code = 'Receiver';
        }

        return new \SoapFault($code, $fault);
    }

    /**
     * Load Soap server with options
     */
    protected function loadSoapServer(): void
    {
        $this->soapServer = new \SoapServer($this->getWsdl(), $this->getSoapOptions());

        if ($this->getOption('functions')) {
            $this->soapServer->addFunction($this->getOption('functions'));
        } else {
            $this->addServiceClass($this->soapServer);
        }

        if ($this->getOption('persistence')) {
            $this->soapServer->setPersistence($this->getOption('persistence'));
        }
    }

    /**
     * @param \SoapServer $server
     */
    protected function addServiceClass(\SoapServer $server): void
    {
        if (isset($this->wrapperClasses[$this->getOption('document')])) {
            $server->setClass(
                $this->wrapperClasses[$this->getOption('document')],
                $this,
                array('soap_fault' => $this->fault)
            );
        } else {
            $server->setObject($this->getServiceClass());
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function doFault($fault): void
    {
        $this->getSoapServer()->fault($fault->faultcode, $fault->faultstring);
    }

    /**
     * {@inheritDoc}
     */
    protected function dispatch($request): string
    {
        ob_start();

        $this->getSoapServer()->handle($request->getRawBody());

        return ob_get_clean();
    }
}
