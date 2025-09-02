<?php

namespace Bdf\Soap\Driver;

use LogicException;
use SoapFault;
use nusoap_client;
use wsdl;

use function array_keys;
use function count;
use function in_array;
use function is_array;

/**
 * NuSoapDriver driver
 */
class NuSoapDriver implements DriverInterface
{
    protected ?nusoap_client $soapClient = null;

    /**
     * {@inheritdoc}
     */
    public function preProcessArguments($name, $arguments, $documentType): array
    {
        if (count($arguments) === 1 && !empty($arguments[0]) && is_array($arguments[0])) {
            $operations = $this->soapClient->wsdl->getOperations();
            
            if (is_array($operations[$name]['input']['parts'])) {
                $methodKeys = array_keys($operations[$name]['input']['parts']);
                $keys       = array_keys($arguments[0]);
                
                foreach ($keys as $key) {
                    if (in_array($key, $methodKeys, true)) {
                        return $arguments[0];
                    }
                }
            }
        }
        
        return $arguments;
    }
    
    /**
     * {@inheritdoc}
     */
    public function preProcessResult($name, $result, $documentType): mixed
    {
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize($wsdl, array $options = []): void
    {
        if (isset($options['proxy_login']) && isset($options['proxy_password'])) {
            $this->soapClient = new nusoap_client(new wsdl($wsdl), true, $options['proxy_host'], $options['proxy_port'], $options['proxy_login'], $options['proxy_password']);
        } else {
            $this->soapClient = new nusoap_client(new wsdl($wsdl), true);
        }

        if (($err = $this->soapClient->getError())) {
            throw new SoapFault('Sender', $err);
        }

        if (isset($options['login'])) {
            $this->soapClient->setCredentials($options['login'], $options['password']);
        }

        if (isset($options['encoding'])) {
            $this->soapClient->soap_defencoding = $options['encoding'];
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function __soapCall($function, array $arguments = array(), $inputHeaders = null, &$outputHeaders = null): mixed
    {
        $result = $this->soapClient->call($function, $arguments);

        if ($this->soapClient->fault) {
            throw new SoapFault('Sender', print_r($result, true));
        }
        
        if (($err = $this->soapClient->getError())) {
            throw new SoapFault('Sender', $err);
        }
        
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function __getLastRequest(): ?string
    {
        return $this->soapClient->request;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getLastResponse(): ?string
    {
        return $this->soapClient->response;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getLastRequestHeaders(): ?string
    {
        return $this->soapClient->requestHeaders ?: null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getLastResponseHeaders(): ?string
    {
        return $this->soapClient->responseHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function __getFunctions(): never
    {
        throw new LogicException('Fonctionnalité non disponible');
    }

    /**
     * {@inheritdoc}
     */
    public function __getTypes(): never
    {
        throw new LogicException('Fonctionnalité non disponible');
    }
}
