<?php

namespace Bdf\Api;

use Bdf\Api\Definition\ServiceDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Api
 *
 * Api configuration
 * Allows developper to configure service, routes and protocol options
 * 
 * @package Bdf\Api
 * @final
 */
class Api
{
    /**
     * Api name
     */
    protected ?string $name = null;
    protected ?ServiceDefinition $definition = null;
    
    /**
     * Service class
     * 
     * @var class-string|null
     */
    protected ?string $serviceClass = null;

    /**
     * Protocol options
     * 
     * format: key => value
     * 
     * @var array
     */
    protected array $protocolOptions = [];

    /**
     * Api listeners
     * 
     * Contains all listener using this api
     * 
     * @var EventSubscriberInterface[]
     */
    protected array $listeners = [];

    /**
     * @var EventSubscriberInterface[]
     */
    protected array $serverListeners = [];


    /**
     * Get the api name
     * 
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the api name
     * 
     * @param string $name
     *
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ServiceDefinition
     */
    public function getDefinition(): ?ServiceDefinition
    {
        return $this->definition;
    }

    /**
     * @param ServiceDefinition $definition
     *
     * @return $this
     */
    public function setDefinition(ServiceDefinition $definition): self
    {
        $this->definition = $definition;

        return $this;
    }
    
    /**
     * Get the service class
     *
     * @return class-string|null
     */
    public function getServiceClass(): ?string
    {
        return $this->serviceClass;
    }

    /**
     * Set service class
     * 
     * @param class-string $class
     *
     * @return self
     * 
     * @api
     */
    public function setServiceClass(string $class): self
    {
        $this->serviceClass = $class;

        return $this;
    }

    /**
     * Get all protocol options from protocol
     * 
     * @param string $protocol   Protocol name: 'soap', 'jsonrpc'
     *
     * @return array
     */
    public function getProtocolOptions(string $protocol): array
    {
        return $this->protocolOptions[$protocol] ?? [];
    }

    /**
     * Set all protocol options
     * 
     * @param string $protocol
     * @param array $options
     *
     * @return self
     *
     * @api
     */
    public function setProtocolOptions(string $protocol, array $options = []): self
    {
        $this->protocolOptions[$protocol] = $options;

        return $this;
    }

    /**
     * Get all api listeners
     * 
     * @return EventSubscriberInterface[]
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }
    
    /**
     * Set all plugins
     * 
     * @param EventSubscriberInterface[] $listeners
     *
     * @return self
     * 
     * @api
     */
    public function setListeners(array $listeners): self
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }

        return $this;
    }
    
    /**
     * Add a listener
     *
     * @param EventSubscriberInterface $listener
     *
     * @return $this
     * 
     * @api
     */
    public function addListener(EventSubscriberInterface $listener): self
    {
        $this->listeners[get_class($listener)] = $listener;

        return $this;
    }

    /**
     * @return EventSubscriberInterface[]
     */
    public function getServerListeners(): array
    {
        return $this->serverListeners;
    }

    /**
     * @param EventSubscriberInterface[] $listeners
     *
     * @return $this
     */
    public function setServerListeners(array $listeners): self
    {
        $this->serverListeners = [];

        return $this->addServerListeners($listeners);
    }

    /**
     * @param EventSubscriberInterface[] $listeners
     *
     * @return $this
     */
    public function addServerListeners(array $listeners): self
    {
        foreach ($listeners as $listener) {
            $this->addServerListener($listener);
        }

        return $this;
    }

    /**
     * @param EventSubscriberInterface $listener
     *
     * @return $this
     */
    public function addServerListener(EventSubscriberInterface $listener): self
    {
        $this->serverListeners[] = $listener;

        return $this;
    }
}
