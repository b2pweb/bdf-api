<?php

namespace Bdf\Soap\Driver;

use SoapClient;

/**
 * SoapClient driver
 * 
 * @todo doit on encoder les parametres en utf8 si l'encoding est iso- ... sur la méthode preProcessArguments
 * @todo Les arguments fournis par un appel $client->run('test') ne seront pas gérés si l'option $documentType vaut 'document-literal'
 */
class SoapClientDriver implements DriverInterface
{
    protected ?SoapClient $soapClient = null;

    /**
     * {@inheritdoc}
     */
    public function preProcessArguments($name, $arguments, $documentType): array
    {
        if ($documentType === 'document-literal') {
            return array($arguments);
        }
        
        return $arguments;
    }
    
    /**
     * {@inheritdoc}
     */
    public function preProcessResult($name, $result, $documentType): mixed
    {
        if ($documentType === 'document-literal' && $result !== null) {
            $resultArray = (array) $result;
            
            return $this->objectToArray($resultArray[$name . 'Result'] ?? $result);
        }
        
        return $result;
    }
    
    /**
     * @todo voir à utiliser le wrapper document
     * 
     * @param mixed $var
     *
     * @return mixed
     */
    protected function objectToArray($var): mixed
    {
        if (is_scalar($var) || is_resource($var) || is_null($var)) {
            return $var;
        }
        
        if ($var instanceof \stdClass) {
            $properties = get_object_vars($var);
            
            if (isset($properties['item']) && count($properties) == 1) {
                if (is_array($properties['item'])) {
                    $var = $properties['item'];
                } elseif (is_object($properties['item'])) {
                    $var = array($properties['item']);
                }
            }
        }
        
        foreach ($var as &$value) {
            $value = $this->objectToArray($value);
        }
        
        return $var;
    }

    /**
     * {@inheritdoc}
     */
    public function __soapCall($function, array $arguments = [], $inputHeaders = null, &$outputHeaders = null): mixed
    {
        return $this->soapClient->__soapCall($function, $arguments, null, $inputHeaders, $outputHeaders);
    }

    /**
     * {@inheritdoc}
     */
    public function __getLastRequest(): ?string
    {
        return $this->soapClient->__getLastRequest();
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getLastResponse(): ?string
    {
        return $this->soapClient->__getLastResponse();
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getLastRequestHeaders(): ?string
    {
        return $this->soapClient->__getLastRequestHeaders();
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getLastResponseHeaders(): ?string
    {
        return $this->soapClient->__getLastResponseHeaders();
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getFunctions(): ?array
    {
        return $this->soapClient->__getFunctions();
    }
    
    /**
     * {@inheritdoc}
     */
    public function __getTypes(): ?array
    {
        return $this->soapClient->__getTypes();
    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize($wsdl, array $options = []): void
    {
        $this->soapClient = new SoapClient($wsdl, $options);
    }
}
