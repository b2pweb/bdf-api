<?php

namespace Bdf\Soap\Wrapper;

use Bdf\Api\Server\ApiServer;

/**
 * Class DefaultWrapper
 *
 * @package Bdf\Soap\Wrapper
 */
class DefaultWrapper
{
    protected ApiServer $server;
    protected array $options = [];
    protected array $callStack = [];
    protected array $request = [
        'headers' => [],
        'method'  => []
    ];

    /**
     * @param ApiServer $server
     * @param array $options
     * 
     * @throws \SoapFault
     */
    public function __construct(ApiServer $server, array $options = [])
    {
        $this->server = $server;

        $this->setOptions($options);

        if ($this->getOption('soap_fault')) {
            throw $this->getOption('soap_fault');
        }

        $this->_loadCallStack();
    }

    /**
     * @param string $key
     * 
     * @return boolean
     */
    public function hasOption($key): bool
    {
        return array_key_exists($key, $this->options);
    }

    /**
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     */
    public function getOption($key, mixed $default = null): mixed
    {
        if (!$this->hasOption($key)) {
            return $default;
        }

        return $this->options[$key];
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $key => $value) {
            $this->setOption($key, $value);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setOption($key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     *
     * @throws \SoapFault
     */
    public function __call(string $method, array $args = []): mixed
    {
        try {
            if (false === next($this->callStack)) {
                return $this->_serialize($this->_processMethod($method, $this->_unserialize($args)));
            } else {
                $this->_processHeader($method, $this->_unserialize($args));
                return null;
            }
        } catch (\SoapFault $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \SoapFault('Receiver', $e->getMessage());
        }
    }

    /**
     * @param mixed $var
     * 
     * @return mixed
     */
    protected function _unserialize($var)
    {
        return $var;
    }

    /**
     * @param mixed $var
     * 
     * @return mixed
     */
    protected function _serialize($var)
    {
        return $var;
    }

    /**
     * @param string $name
     * @param array  $arguments
     */
    protected function _processHeader($name, $arguments = []): void
    {
        $this->request['headers'][] = ['name' => $name, 'arguments' => $arguments];
    }

    /**
     * @param string $name
     * @param array $arguments
     * 
     * @return mixed
     */
    protected function _processMethod($name, $arguments = [])
    {
        return $this->server->call($name, $arguments);
    }

    /**
     * Charge la pile des appels à effectuer sur le service
     */
    protected function _loadCallStack(): void
    {
        $this->callStack = [];

        $document = simplexml_load_string($this->getOption('soap_request', file_get_contents('php://input')));

        if (false === $document) {
            throw new \SoapFault('Sender', 'Invalid SOAP Request !');
        }

        foreach ($document->xpath('/*[local-name()="Envelope"]/*[local-name()="Header"]/*') as $header) {
            $this->callStack[] = $header->getName();
        }

        foreach ($document->xpath('/*[local-name()="Envelope"]/*[local-name()="Body"]/*[1]') as $method) {
            $this->callStack[] = $method->getName();

            break;
        }
    }
}
