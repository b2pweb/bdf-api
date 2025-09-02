<?php

namespace Bdf\Api\Listeners;

use Bdf\Api\Events\KernelEvents;
use Bdf\Api\Events\ApiEvent;
use Bdf\Api\Events\ProtocolEvent;
use Bdf\Api\Keys\RepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ApiKeyListener
 */
class ApiKeyListener implements EventSubscriberInterface
{
    protected RepositoryInterface $repository;

    /**
     * @param RepositoryInterface $repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return array(
            KernelEvents::API        => [['onKernelApi', 32]],
            KernelEvents::PROTOCOL   => 'onKernelProtocol',
        );
    }

    /**
     * @param ApiEvent $event
     */
    public function onKernelApi(ApiEvent $event): void
    {
        $event->getRequest()->setApiKey(
            $this->repository->get($event->getRequest()->attributes->get('key'))
        );
    }

    /**
     * @param ProtocolEvent $event
     *
     * @throws \Exception
     */
    public function onKernelProtocol(ProtocolEvent $event): void
    {
        $apiKey = $event->getRequest()->getApiKey();

        if (!$apiKey->isValid()) {
            throw new \Exception('Access denied for api key "' . $apiKey->getKey() . '"', 403);
        }
    }
}
