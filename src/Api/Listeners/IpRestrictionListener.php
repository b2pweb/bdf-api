<?php

namespace Bdf\Api\Listeners;

use Bdf\Api\Events\KernelEvents;
use Bdf\Api\Events\ProtocolEvent;
use Bdf\Api\Firewall\FirewallInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * IpRestrictionListener
 */
class IpRestrictionListener implements EventSubscriberInterface
{
    protected FirewallInterface $repository;

    /**
     * @param FirewallInterface $repository
     */
    public function __construct(FirewallInterface $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::PROTOCOL => 'onKernelProtocol',
        );
    }

    /**
     * @param ProtocolEvent $event
     *
     * @throws \Exception
     */
    public function onKernelProtocol(ProtocolEvent $event): void
    {
        $request = $event->getRequest();
        
        if (!$this->repository->isAllowed($request->getClientIp())) {
            throw new \Exception('Access denied for ip "' . $request->getClientIp() . '"', 403);
        }
    }
}