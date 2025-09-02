<?php

namespace Bdf\Soap;

use Bdf\Api\Server\ClientInterface;
use Bdf\Soap\Driver\DriverInterface;
use Bdf\Soap\Driver\NuSoapDriver;
use Bdf\Soap\Driver\SoapClientDriver;
use SoapHeader;

/**
 * @package Bdf\Soap
 */
class SoapClient implements ClientInterface
{
    protected ?DriverInterface $soapClient = null;
    protected string $driver = 'SoapClient';

    /**
     * @var array<string, class-string<DriverInterface>>
     */
    protected array $driverClassMap = [
        'SoapClient' => SoapClientDriver::class,
        'NuSoap'     => NuSoapDriver::class,
    ];
    protected ?string $wsdl = null;
    
    /**
     * @var array
     */
    protected array $soapOptions = [
        'soap_version'      => \SOAP_1_2,
        'features'          => \SOAP_SINGLE_ELEMENT_ARRAYS,
        'trace'             => true,
        'keep_alive'        => false,
    ];
    
    /**
     * @var array
     */
    protected array $options = [];
    
    /**
     * @var string|null
     */
    private ?string $lastMethod = null;
    
    /**
     * @var array
     */
    private array $permanentSoapInputHeaders = [];
    
    /**
     * @var array
     */
    private array $soapInputHeaders = [];
    
    /**
     * @var array
     */
    private array $soapOutputHeaders = [];

    /**
     * @param string|null $wsdl
     * @param array $options
     */
    public function __construct(?string $wsdl = null, array $options = [])
    {
        $this->setOptions(array('wsdl' => $wsdl) + $options);
    }

    public function setWsdl(?string $wsdl): void
    {
        $this->wsdl = $wsdl;
    }
    
    public function getWsdl(): ?string
    {
        return $this->wsdl;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->addOption($name, $value);
        }
    }
    
    /**
     * @param string $name
     * @param mixed $value
     */
    public function addOption(string $name, mixed $value): void
    {
        switch ($name) {
            case 'cache_wsdl':
            case 'classmap':
            case 'compression':
            case 'encoding':
            case 'exceptions':
            case 'features':
            case 'keep_alive':
            case 'location':
            case 'local_cert':
            case 'login':
            case 'passphrase':
            case 'password':
            case 'proxy_host':
            case 'proxy_port':
            case 'proxy_login':
            case 'proxy_password':
            case 'soap_version':
            case 'ssl_method':
            case 'style':
            case 'stream_context':
            case 'trace':
            case 'typemap':
            case 'uri':
            case 'use':
            case 'user_agent':
                $this->soapOptions[$name] = $value;
                break;

            case 'class':
            case 'functions':
            case 'object':
            case 'persistence':
            case 'document':  //document-literal | rpc-encoded
                $this->options[$name] = $value;
                break;

            case 'wsdl':
                $this->setWsdl($value);
                break;
            
            case 'driver':
                $this->setDriver($value);
                break;
        }
    }
    
    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    
    /**
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption($key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }
    
    /**
     * @return array
     */
    public function getSoapOptions(): array
    {
        return $this->soapOptions;
    }

    /**
     * @param string $driver
     */
    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }
    
    /**
     * @param string $driver
     * @param class-string<DriverInterface> $className
     */
    public function registerDriverClassMap(string $driver, string $className): void
    {
        $this->driverClassMap[$driver] = $className;
    }

    /**
     * @see ClientInterface::getLastRequest
     */
    public function getLastRequest(): ?string
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastRequest();
        }

        return '';
    }

    /**
     * @see ClientInterface::getLastResponse
     */
    public function getLastResponse(): ?string
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastResponse();
        }

        return '';
    }
    
    /**
     * @see ClientInterface::getLastRequestHeaders
     */
    public function getLastRequestHeaders(): ?string
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastRequestHeaders();
        }
        return '';
    }

    /**
     * @see ClientInterface::getLastResponseHeaders
     */
    public function getLastResponseHeaders(): ?string
    {
        if ($this->soapClient !== null) {
            return $this->soapClient->__getLastResponseHeaders();
        }
        return '';
    }
    
    /**
     * Return a list of available functions
     */
    public function getFunctions(): ?array
    {
        return $this->getSoapClient()->__getFunctions();
    }

    /**
     * Return a list of SOAP types
     */
    public function getTypes(): ?array
    {
        return $this->getSoapClient()->__getTypes();
    }
    
    /**
     * Retrieve last invoked method
     */
    public function getLastMethod(): ?string
    {
        return $this->lastMethod;
    }
    
    /**
     * Add SOAP input header
     *
     * @param  SoapHeader $header
     * @param  bool $permanent
     */
    public function addSoapInputHeader(SoapHeader $header, bool $permanent = false): void
    {
        if ($permanent) {
            $this->permanentSoapInputHeaders[] = $header;
        } else {
            $this->soapInputHeaders[] = $header;
        }
    }

    /**
     * Reset SOAP input headers
     */
    public function resetSoapInputHeaders(): void
    {
        $this->soapInputHeaders = [];
    }

    /**
     * Get last SOAP output headers
     *
     * @return array
     */
    public function getLastSoapOutputHeaderObjects(): array
    {
        return $this->soapOutputHeaders;
    }
    
    /**
     * Perform a SOAP call
     *
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments = []): mixed
    {
        $soapClient = $this->getSoapClient();

        $this->lastMethod = $name;

        $soapHeaders = array_merge($this->permanentSoapInputHeaders, $this->soapInputHeaders);

        $result = $soapClient->__soapCall(
            $name,
            $soapClient->preProcessArguments($name, $arguments, $this->getOption('document')), 
            (count($soapHeaders) > 0) ? $soapHeaders : null,
            $this->soapOutputHeaders
        );

        $this->soapInputHeaders = [];

        return $soapClient->preProcessResult($name, $result, $this->getOption('document'));
    }
    
    /**
     * @see ClientInterface::call
     * 
     * Send an RPC request to the service for a specific method.
     *
     * @param  string $method Name of the method we want to call.
     * @param  array  $params List of parameters for the method.
     * @return mixed Returned results.
     */
    public function call($method, $params = []): mixed
    {
        return $this->__call($method, (array)$params);
    }
    
    /**
     * @param  DriverInterface $soapClient
     */
    public function setSoapClient(DriverInterface $soapClient): void
    {
        $this->soapClient = $soapClient;
    }
    
    /**
     * @return DriverInterface
     */
    public function getSoapClient(): DriverInterface
    {
        return $this->soapClient ??= $this->initSoapClient();
    }
    
    /**
     * @return DriverInterface
     */
    protected function initSoapClient(): DriverInterface
    {
        if (!isset($this->driverClassMap[$this->driver])) {
            throw new Exception\InvalidArgumentException('Driver soap not found "' . $this->driver . '"');
        }
        
        $className = $this->driverClassMap[$this->driver];
        
        $driver = new $className;
        $driver->initialize($this->getWsdl(), $this->getSoapOptions());
        
        return $driver;
    }
}
