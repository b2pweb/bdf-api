<?php

namespace Bdf\Api;

use Bdf\Api\Events\ApiEvent;
use Bdf\Api\Events\KernelEvents;
use Bdf\Api\Events\ProtocolEvent;
use Bdf\Api\Protocol\ProtocolFactory;
use Bdf\Api\Providers\ApiProviderInterface;
use Bdf\Api\Server\Listeners\ExceptionBridgeListener;
use Bdf\Routing\Builder\RouterBuilder;
use Closure;
use LogicException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function method_exists;

/**
 * Api Application
 */
class Application implements HttpKernelInterface, ContainerInterface, EventDispatcherInterface, TerminableInterface
{
    /**
     * @var array<string, Api|(Closure(): Api)>
     */
    private array $apis = [];

    public function __construct(
        private readonly HttpKernelInterface $kernel,
    ) {}

    public function getContainer(): ContainerInterface
    {
        if ($this->kernel instanceof ContainerInterface) {
            return $this->kernel;
        }

        if ($this->kernel instanceof KernelInterface) {
            return $this->kernel->getContainer();
        }

        throw new LogicException('The kernel is not a container');
    }

    public function getKernel(): HttpKernelInterface
    {
        return $this->kernel;
    }

    public function eventDispatcher(): EventDispatcherInterface
    {
        if (method_exists($this->kernel, 'eventDispatcher')) {
            return $this->kernel->eventDispatcher();
        }

        return $this->get('event_dispatcher');
    }

    public function boot(): void
    {
        $this->kernel->boot();
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $id): mixed
    {
        return $this->getContainer()->get($id);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->getContainer()->has($id);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(object $event, ?string $eventName = null): object
    {
        return $this->eventDispatcher()->dispatch($event, $eventName);
    }

    /**
     * Attache an api provider
     *
     * @param ApiProviderInterface $provider
     * @param string $path
     * @param array $defaults
     */
    public function attach(ApiProviderInterface $provider, string $path, array $defaults = []): void
    {
        // @todo handle if _api is already defined
        $defaults['_api'] = get_class($provider);
        $this->apis[get_class($provider)] = function() use($provider) {
            $api = new Api();

            $provider->configureApi($api, $this);
            $api->addServerListener(new ExceptionBridgeListener($this, $this->requestStack(), $this->eventDispatcher()));

            return $api;
        };

        if (class_exists(RouterBuilder::class)) {
            // Legacy support for bdf-routing
            $builder = new RouterBuilder();
            $builder->any($path)->with($defaults);
            $builder->build($this->get('router')->routes());
        } else {
            $router = $this->get('router');
            assert($router instanceof RouterInterface);

            $router->getRouteCollection()->add(
                'api_' . md5($path),
                new Route($path, $defaults)
            );
        }
    }

    /**
     * Register a new API provider
     *
     * @param ApiProviderInterface|\Bdf\Web\Providers\ServiceProviderInterface $provider
     * @return void
     */
    public function register($provider): void
    {
        if ($provider instanceof ApiProviderInterface && method_exists($provider, 'path')) {
            $this->attach($provider, $provider->path());
        }

        // Legacy bdf application
        if ($provider instanceof \Bdf\Web\Providers\ServiceProviderInterface) {
            $this->kernel->register($provider);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(BaseRequest $request, int $type = HttpKernelInterface::MAIN_REQUEST, bool $catch = true): Response
    {
        $request = ApiRequest::createFromBase($request);

        try {
            return $this->handleRaw($request, $type);
        } catch (Throwable $e) {
            if (false === $catch) {
                $this->finishRequest($request, $type);

                throw $e;
            }

            return $this->handleException($e, $request, $type);
        }
    }

    /**
     * @param ApiRequest $request
     * @param int        $type
     *
     * @return Response
     *
     * @throws \Throwable
     */
    protected function handleRaw(ApiRequest $request, int $type = self::MAIN_REQUEST): Response
    {
        $this->requestStack()->push($request);

        $dispatcher = $this->eventDispatcher();
        $dispatcher->dispatch(new RequestEvent($this, $request, $type), KernelEvents::REQUEST);

        $api = $this->resolveApi($request);

        foreach ($api->getListeners() as $listener) {
            $dispatcher->addSubscriber($listener);
        }

        $dispatcher->dispatch(new ApiEvent($this, $api, $request, $type), KernelEvents::API);

        if ($this->has('api.protocolFactory')) {
            $protocolFactory = $this->get('api.protocolFactory');
        } elseif ($this->has(ProtocolFactory::class)) {
            $protocolFactory = $this->get(ProtocolFactory::class);
        } else {
            $protocolFactory = new ProtocolFactory($this->getContainer());
        }

        assert($protocolFactory instanceof ProtocolFactory);

        $protocol = $protocolFactory->create(
            $request->getApiProtocol(),
            $api->getProtocolOptions($request->getApiProtocol())
        );

        try {
            $dispatcher->dispatch(new ProtocolEvent($this, $protocol, $request, $type), KernelEvents::PROTOCOL);
        } catch (\Throwable $e) {
            $request->setException($e);
        }

        return $this->filterResponse($protocol->handle($request), $request, $type);
    }

    /**
     * @param ApiRequest $request
     *
     * @return Api
     */
    protected function resolveApi(ApiRequest $request): Api
    {
        if (!$request->attributes->has('_api')) {
            throw new NotFoundHttpException(sprintf('Unable to find the api for path "%s". The route is wrongly configured.', $request->getPathInfo()));
        }

        $api = $request->attributes->get('_api');

        if ($api instanceof Api) {
            return $api;
        }

        if (is_string($api)) {
            $resolvedApi = $this->apis[$api] ?? null;

            if ($resolvedApi === null) {
                throw new NotFoundHttpException(sprintf('Unable to find the api for path "%s". The route is wrongly configured.', $request->getPathInfo()));
            }

            if ($resolvedApi instanceof Closure) {
                $resolvedApi = $resolvedApi();
            }

            assert($resolvedApi instanceof Api);
            $request->attributes->set('_api', $resolvedApi);

            return $resolvedApi;
        }

        if ($api instanceof Closure) {
            $request->attributes->set('_api', $api = $api());
            return $api;
        }

        throw new NotFoundHttpException(sprintf('Unable to find the api for path "%s". The route is wrongly configured.', $request->getPathInfo()));
    }

    /**
     * @param \Throwable  $e
     * @param BaseRequest $request
     * @param int         $type
     *
     * @return Response
     *
     * @throws \Throwable
     */
    protected function handleException(\Throwable $e, BaseRequest $request, int $type): Response
    {
        $event = new ExceptionEvent($this, $request, $type, $e);

        $this->dispatch($event, KernelEvents::EXCEPTION);

        $e = $event->getThrowable();

        if (!$event->hasResponse()) {
            $this->finishRequest($request, $type);

            throw $e;
        }

        $response = $event->getResponse();

        // the developer asked for a specific status code
        if ($response->headers->has('X-Status-Code')) {
            $response->setStatusCode($response->headers->get('X-Status-Code'));

            $response->headers->remove('X-Status-Code');
        } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($e instanceof HttpExceptionInterface) {
                // keep the HTTP status code and headers
                $response->setStatusCode($e->getStatusCode());
                $response->headers->add($e->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }

        try {
            return $this->filterResponse($response, $request, $type);
        } catch (\Exception $e) {
            return $response;
        }
    }

    /**
     * @param Response $response
     * @param BaseRequest  $request
     * @param int          $type
     *
     * @return Response
     */
    protected function filterResponse(Response $response, BaseRequest $request, int $type): Response
    {
        $event = new ResponseEvent($this, $request, $type, $response);

        $this->dispatch($event, KernelEvents::RESPONSE);

        $this->finishRequest($request, $type);

        return $event->getResponse();
    }

    /**
     * @param BaseRequest $request
     * @param int         $type
     */
    protected function finishRequest(BaseRequest $request, int $type): void
    {
        $this->dispatch(new FinishRequestEvent($this, $request, $type), KernelEvents::FINISH_REQUEST);
        $this->requestStack()->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(BaseRequest $request, Response $response): void
    {
        $this->dispatch(new TerminateEvent($this, $request, $response), KernelEvents::TERMINATE);
    }

    private function requestStack(): RequestStack
    {
        if ($this->has('requestStack')) {
            return $this->get('requestStack');
        }

        if ($this->has(RequestStack::class)) {
            return $this->get(RequestStack::class);
        }

        if ($this->has('request_stack')) {
            return $this->get('request_stack');
        }

        throw new LogicException('The request stack service is not available in the container');
    }
}
