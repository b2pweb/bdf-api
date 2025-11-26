<?php

namespace Bdf\Api\Server;

use Bdf\Api\Definition\ServiceDefinition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interface ServerInterface
 *
 * @package Bdf\Api\Server
 */
interface ServerInterface
{
    /**
     * @return ServiceDefinition
     */
    public function getDefinition();

    /**
     * @param ServiceDefinition $definition
     *
     * @return ServerInterface
     */
    public function setDefinition(ServiceDefinition $definition);
    
    /**
     * @param string|string[] $functions
     * 
     * @return ServerInterface
     */
    public function addFunction($functions);

    /**
     * @param string|object $class
     * @param array         $args
     *
     * @return ServerInterface
     */
    public function setClass($class, $args = null);

    /**
     * @param array $classmap
     * 
     * @return ServerInterface
     */
    public function setClassmap(array $classmap);
    
    /**
     * Indicate fault response
     *
     * @param  string $fault
     * @param  int    $code
     * @param  mixed  $data
     * 
     * @return \Exception
     */
    public function fault($fault, $code = null, $data = null);

    /**
     * @return \Exception
     */
    public function getFault();

    /**
     * @return boolean
     */
    public function isFaulted();

    /**
     * @param EventSubscriberInterface[] $listeners
     *
     * @return ServerInterface
     */
    public function addListeners(array $listeners);

    /**
     * @param EventSubscriberInterface $listener
     *
     * @return ServerInterface
     */
    public function addListener(EventSubscriberInterface $listener);

    /**
     * @param EventSubscriberInterface[] $listeners
     *
     * @return ServerInterface
     */
    public function removeListeners(array $listeners);

    /**
     * @param EventSubscriberInterface $listener
     *
     * @return ServerInterface
     */
    public function removeListener(EventSubscriberInterface $listener);

    /**
     * Set flag indicating whether or not to auto-emit response
     *
     * @param bool $flag
     *
     * @return ServerInterface
     */
    public function setAutoEmitResponse($flag);

    /**
     * @param Request\RequestInterface|null $request
     *
     * @return Response\ResponseInterface|null
     */
    public function handle(?Request\RequestInterface $request = null);
}
