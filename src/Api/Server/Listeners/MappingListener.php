<?php

namespace Bdf\Api\Server\Listeners;

use Bdf\Api\Mapping\MapperInterface;
use Bdf\Api\Server\Events\PostServiceEvent;
use Bdf\Api\Server\Events\PreServiceEvent;
use Bdf\Api\Server\Events\ServerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MappingListener
 *
 * @package Bdf\Api\Server\Listeners
 */
class MappingListener implements EventSubscriberInterface
{
    protected MapperInterface $mapper;


    /**
     * MapperListener constructor.
     *
     * @param MapperInterface $mapper
     */
    public function __construct(MapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ServerEvents::PRE_SERVICE  => 'onPreService',
            ServerEvents::POST_SERVICE => 'onPostService',
        ];
    }

    /**
     * @param PreServiceEvent $event
     */
    public function onPreService(PreServiceEvent $event): void
    {
        $event->setParameters($this->mapper->mapMethod($event->getMethod(), $event->getParameters()));
    }

    /**
     * @param PostServiceEvent $event
     */
    public function onPostService(PostServiceEvent $event): void
    {
        $event->setReturnValue($this->mapper->mapMethodReturn($event->getMethod(), $event->getReturnValue()));
    }
}
