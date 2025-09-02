<?php

namespace Bdf\Api\Listeners;

use Bdf\Api\Events\KernelEvents;
use Bdf\Api\Events\ProtocolEvent;
use Bdf\Security\SecurityLocatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * BasicHttpAuthListener
 */
class BasicHttpAuthListener implements EventSubscriberInterface
{
    /**
     * @var SecurityLocatorInterface
     */
    protected $security;

    /**
     * Constructor
     * 
     * @param SecurityLocatorInterface $security
     */
    public function __construct(SecurityLocatorInterface $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::PROTOCOL => 'onKernelProtocol',
        );
    }

    /**
     * @param ProtocolEvent $event
     */
    public function onKernelProtocol(ProtocolEvent $event)
    {
        $request = $event->getRequest();

        if (!$event->getProtocol()->isServiceMapRequest($request)) {
            $this->security->login($request->getUser(), $request->getPassword());
        }
    }
}