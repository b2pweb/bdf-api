<?php

namespace Bdf\Api\Server\Events;

use Bdf\Api\Definition\MethodDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PreServiceEvent
 *
 * @package Bdf\Api\Server\Events
 */
class PreServiceEvent extends Event
{
    /**
     * @var MethodDefinition
     */
    protected $methodDefinition;

    /**
     * @var array
     */
    protected $parameters;


    /**
     * PreServiceEvent constructor.
     *
     * @param MethodDefinition $methodDefinition
     * @param array $parameters
     */
    public function __construct(MethodDefinition $methodDefinition, array $parameters)
    {
        $this->methodDefinition = $methodDefinition;
        $this->parameters = $parameters;
    }

    /**
     * @return MethodDefinition
     */
    public function getMethod()
    {
        return $this->methodDefinition;
    }

    /**
     * @param MethodDefinition $methodDefinition
     */
    public function setMethod(MethodDefinition $methodDefinition)
    {
        $this->methodDefinition = $methodDefinition;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}
