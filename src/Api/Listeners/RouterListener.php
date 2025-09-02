<?php

namespace Bdf\Api\Listeners;

use Bdf\Routing\Exception\MethodNotAllowedException;
use Bdf\Routing\Exception\RouteNotFoundException;
use Bdf\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * RouterListener
 *
 * @todo Use \Bdf\Routing\RouterListener
 */
class RouterListener implements EventSubscriberInterface
{
    /**
     * @var Router|RouterInterface
     */
    protected $router;

    public function __construct(Router|RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 32]]
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        try {
            $matches = $this->router->matchRequest($request);
        } catch (MethodNotAllowedException|\Symfony\Component\Routing\Exception\MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), implode(', ', $e->getAllowedMethods()));

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        } catch (RouteNotFoundException|\Symfony\Component\Routing\Exception\RouteNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

            if ($referer = $request->headers->get('referer')) {
                $message .= sprintf(' (from "%s")', $referer);
            }

            throw new NotFoundHttpException($message, $e);
        }

        $request->attributes->add($matches);
        
        $api = $request->attributes->get('_api');
        
        if ($api instanceof \Closure) {
            $request->attributes->set('_api', $api());
        }
    }
}
