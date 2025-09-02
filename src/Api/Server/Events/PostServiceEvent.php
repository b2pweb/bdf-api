<?php

namespace Bdf\Api\Server\Events;

use Bdf\Api\Definition\MethodDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PostServiceEvent
 *
 * @package Bdf\Api\Server\Events
 */
class PostServiceEvent extends Event
{
    /**
     * @var MethodDefinition
     */
    protected $methodDefinition;

    /**
     * @var mixed
     */
    protected $returnValue;


    /**
     * PostServiceEvent constructor.
     *
     * @param MethodDefinition $methodDefinition
     * @param mixed $returnValue
     */
    public function __construct(MethodDefinition $methodDefinition, $returnValue)
    {
        $this->methodDefinition = $methodDefinition;
        $this->returnValue = $returnValue;
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
     * @return mixed
     */
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @param mixed $returnValue
     */
    public function setReturnValue($returnValue)
    {
        $this->returnValue = $returnValue;
    }
}
