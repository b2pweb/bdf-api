<?php

namespace Bdf\Api\Server\Listeners;

use Bdf\Api\Events\KernelEvents;
use Bdf\Api\Server\Events\ServiceExceptionEvent;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Listener for forward api service exception to symfony kernel exception listeners
 */
class ExceptionBridgeListener implements EventSubscriberInterface
{
    private HttpKernelInterface $kernel;
    private RequestStack $requestStack;
    private EventDispatcherInterface|\Bdf\Web\Application $dispatcher;

    public function __construct(
        HttpKernelInterface $kernel,
        ?RequestStack $requestStack = null,
        ?EventDispatcherInterface $dispatcher = null,
    ) {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack ?? ($kernel instanceof ContainerInterface ? $kernel->get('requestStack') : throw new InvalidArgumentException('The requestStack is required when the kernel is not a container'));
        $this->dispatcher = $dispatcher ?? $kernel;
    }

    /**
     * Handle service exception
     *
     * @param ServiceExceptionEvent $event
     */
    public function onException(ServiceExceptionEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $sfEvent = new ExceptionEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $event->exception());

        $this->dispatcher->dispatch($sfEvent, KernelEvents::EXCEPTION);
        $event->setException($sfEvent->getThrowable());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ServiceExceptionEvent::class => 'onException',
        ];
    }
}
