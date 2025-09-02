<?php

namespace Bdf\Api\Protocol;

use Psr\Container\ContainerInterface;

/**
 * Class ProtocolFactory
 *
 * @package Bdf\Api\Protocol
 */
class ProtocolFactory
{
    protected ContainerInterface $di;

    /**
     * @var array<string, class-string<ProtocolInterface>>
     */
    protected array $protocols = [
        'jsonrpc' => 'Bdf\JsonRpc\JsonRpcProtocol',
        'soap'    => 'Bdf\Soap\SoapProtocol',
    ];

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @param string $protocol
     * @param array  $options
     *
     * @return ProtocolInterface
     *
     * @throws \Exception
     */
    public function create(string $protocol, array $options = []): ProtocolInterface
    {
        if (empty($this->protocols[$protocol])) {
            throw new \Exception('Unsupported protocol "' . $protocol . '"');
        }

        return new $this->protocols[$protocol]($this->di, $options);
    }
    
    /**
     * Register a protocol class
     * 
     * @param string $protocol
     * @param class-string<ProtocolInterface> $classname
     */
    public function register(string $protocol, string $classname): void
    {
        $this->protocols[$protocol] = $classname;
    }
    
    /**
     * Unregister a protocol
     * 
     * @param string $protocol
     */
    public function unregister(string $protocol): void
    {
        unset($this->protocols[$protocol]);
    }
}
