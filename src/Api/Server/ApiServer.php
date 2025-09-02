<?php

namespace Bdf\Api\Server;

use Bdf\Api\ApiService;
use Bdf\Api\Definition\MethodDefinition;
use Bdf\Api\Definition\ServiceDefinition;
use Bdf\Api\Exception\InvalidArgumentException;
use Bdf\Api\Exception\RuntimeException;
use Bdf\Api\Server\Events\PostServiceEvent;
use Bdf\Api\Server\Events\PreServiceEvent;
use Bdf\Api\Server\Events\ServerEvents;
use Bdf\Api\Server\Events\ServiceExceptionEvent;
use Bdf\Exception\ErrorStacker;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function restore_error_handler;
use function set_error_handler;

/**
 * Class ApiServer
 *
 * @package Bdf\Api\Server
 */
class ApiServer implements ServerInterface
{
    /**
     * @var array
     */
    protected $options = [
        'classmap' => null,
    ];

    /**
     * @var ServiceDefinition
     */
    protected $definition;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var bool
     */
    protected $autoEmitResponse = true;

    /**
     * @var Request\RequestInterface
     */
    protected $request;

    /**
     * @var Response\ResponseInterface
     */
    protected $response;

    /**
     * @var Exception
     */
    protected $fault;

    /**
     * @var array [code] => [mappingCode]
     */
    protected $mappingCodes = [];

    /**
     * @var ErrorStacker
     */
    private $errorStacker;


    /**
     * ApiServer constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->eventDispatcher = new EventDispatcher();

        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $option
     * @param mixed $default
     *
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        return isset($this->options[$option]) ? $this->options[$option] : $default;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $method = 'set' . ucfirst($name);

            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->addOption($name, $value);
            }
        }

        return $this;
    }

    /**
     * @param string $option
     * @param mixed $value
     *
     * @return $this
     */
    public function addOption($option, $value)
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addListeners(array $listeners)
    {
        foreach ($listeners as $listener) {
            $this->addListener($listener);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addListener(EventSubscriberInterface $listener)
    {
        $this->eventDispatcher->addSubscriber($listener);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListeners(array $listeners)
    {
        foreach ($listeners as $listener) {
            $this->removeListener($listener);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener(EventSubscriberInterface $listener)
    {
        $this->eventDispatcher->removeSubscriber($listener);

        return $this;
    }

    /**
     * @return array
     */
    public function getMappingCodes()
    {
        return $this->mappingCodes;
    }

    /**
     * @param array $codes
     *
     * @return $this
     */
    public function setMappingCodes(array $codes)
    {
        $this->mappingCodes = $codes;

        return $this;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getMappingCode($code)
    {
        return isset($this->mappingCodes[$code]) ? $this->mappingCodes[$code] : $code;
    }

    /**
     * @param string $code
     * @param string $mapping
     *
     * @return $this
     */
    public function setMappingCode($code, $mapping)
    {
        $this->mappingCodes[$code] = $mapping;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param ServiceDefinition $definition
     *
     * @return $this
     */
    public function setDefinition(ServiceDefinition $definition)
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Returns request object
     *
     * @return Request\RequestInterface
     */
    public function getRequest()
    {
        if (null === $this->request) {
            $this->setRequest(new Request\CustomRequest());
        }

        return $this->request;
    }

    /**
     * Set request object
     *
     * @param Request\RequestInterface $request
     *
     * @return $this
     */
    public function setRequest(Request\RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Returns response object
     *
     * @return Response\ResponseInterface
     */
    public function getResponse()
    {
        if (null === $this->response) {
            $this->setResponse(new Response\JsonResponse());
        }

        return $this->response;
    }

    /**
     * Set response object
     *
     * @param Response\ResponseInterface $response
     *
     * @return $this
     */
    public function setResponse(Response\ResponseInterface $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return array
     */
    public function getClassmap()
    {
        return $this->getOption('classmap', []);
    }

    /**
     * {@inheritDoc}
     */
    public function setClassmap(array $classmap)
    {
        $this->addOption('classmap', $classmap);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addFunction($functions)
    {
        $this->addOption('functions', (array) $functions);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setClass($class, $args = null)
    {
        if (is_object($class)) {
            $this->addOption('object', $class);
        } else {
            $this->addOption('class', [$class, (array) $args]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fault($fault, $code = null, $data = null)
    {
        if ($fault instanceof Exception) {
            $message = $fault->getMessage();
            $code    = $fault->getCode() ?: $code;
        } elseif(is_string($fault)) {
            $message = $fault;
        } else {
            $message = 'Unknown error';
        }

        return $this->fault = $this->instanciateFault($message, $this->getMappingCode($code), $data);
    }
    
    /**
     * {@inheritdoc}
     */
    public function isFaulted()
    {
        return $this->fault !== null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFault()
    {
        return $this->fault;
    }

    /**
     * {@inheritdoc}
     */
    public function setAutoEmitResponse($flag)
    {
        $this->autoEmitResponse = (bool) $flag;
        
        return $this;
    }

    /**
     * Will we auto-emit the response?
     *
     * @return bool
     */
    public function autoEmitResponse()
    {
        return $this->autoEmitResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Request\RequestInterface $request = null)
    {
        if ($request == null) {
            $request = $this->getRequest();
        }

        $errorStacker = $this->getErrorStacker();

        if ($errorStacker) {
            $errorStacker->start(E_USER_ERROR);
        } else {
            $previous = set_error_handler(
                function ($errno, $errstr) {
                    throw $this->fault($errstr, $errno);
                },
                E_USER_ERROR
            );
        }

        try {
            $this->doHandle($request);
        } finally {
            if ($errorStacker) {
                $errorStacker->stop();
            } else {
                restore_error_handler();

                if ($previous) {
                    set_error_handler($previous);
                }
            }
        }

        $response = $this->getResponse();
        $response->prepare($request);

        if ($this->autoEmitResponse()) {
            $response->send();

            return null;
        }

        return $response;
    }

    /**
     * @param Request\RequestInterface $request
     */
    protected function doHandle(Request\RequestInterface $request)
    {
        if ($this->isFaulted()) {
            $this->doFault($this->fault);
            return;
        }

        try {
            $request->prepare();
            $request->validate();

            $result = $this->dispatch($request);

            if ($this->fault) {
                throw $this->fault;
            }
        } catch (Exception $e) {
            $this->doFault($this->fault($e->getMessage(), $e->getCode(), $e));
            return;
        }

        $this->getResponse()->setResult($result);
    }

    /**
     * @param Exception $fault
     */
    protected function doFault($fault)
    {
        $this->getResponse()->setError($fault);
    }

    /**
     * @param string $fault
     * @param mixed $code
     * @param mixed $data
     * 
     * @return RuntimeException
     */
    protected function instanciateFault($fault = null, $code = null, $data = null)
    {
        if (empty($code)) {
            $code = 500;
        }

        return new RuntimeException($fault, $code);
    }

    /**
     * Dispatch method
     *
     * @param Request\RequestInterface $request
     * 
     * @return mixed
     */
    protected function dispatch($request)
    {
        return $this->call($request->getMethod(), $request->getParams());
    }

    /**
     * Call the api method
     *
     * @param string $name
     * @param array $parameters
     *
     * @return mixed
     */
    public function call($name, array $parameters = [])
    {
        $methodDefinition = $this->getMethodDefinition($name);

        /** @var PreServiceEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PreServiceEvent($methodDefinition, $this->prepareParameters($methodDefinition, $parameters)),
            ServerEvents::PRE_SERVICE
        );

        $callback = $this->createCallableEndpoint($name);

        try {
            $return = $callback(...array_values($event->getParameters()));
        } catch (Exception $e) {
            $this->eventDispatcher->dispatch($event = new ServiceExceptionEvent($e));

            if ($e = $event->exception()) {
                throw $e;
            }
        }

        /** @var PostServiceEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PostServiceEvent($methodDefinition, $return),
            ServerEvents::POST_SERVICE
        );

        return $event->getReturnValue();
    }

    /**
     * Returns a callable api endpoint
     *
     * @param string $name
     *
     * @return callable
     *
     * @throws RuntimeException If no endpoint configured
     */
    protected function createCallableEndpoint($name)
    {
        if ($this->getOption('object') || $this->getOption('class')) {
            return [$this->getServiceClass(), $name];
        }

        if ($this->getOption('functions') && in_array($name, $this->getOption('functions'))) {
            return $name;
        }

        throw new RuntimeException('No endpoint configured', 500);
    }

    /**
     * @param MethodDefinition $methodDefinition
     * @param array $parameters
     *
     * @return array
     *
     * @throws InvalidArgumentException If missing required parameter
     *
     * @todo Gérer les paramètres par la position au lieu du nom ? (ne pas oublier le ksort si modif effectuée !)
     */
    protected function prepareParameters(MethodDefinition $methodDefinition, array $parameters)
    {
        $result = [];

        foreach ($methodDefinition->getParameters() as $parameterDefinition) {
            if (isset($parameters[$parameterDefinition->getName()])) {
                $value = $parameters[$parameterDefinition->getName()];
            } elseif (isset($parameters[$parameterDefinition->getPosition()])) {
                $value = $parameters[$parameterDefinition->getPosition()];
            } elseif ($parameterDefinition->isOptional()) {
                $value = $parameterDefinition->getDefaultValue();
            } else {
                throw new InvalidArgumentException('Missing required parameter: ' . $parameterDefinition->getName(), 400);
            }

            $result[$parameterDefinition->getName()] = $value;
        }

        return $result;
    }

    /**
     * @return ApiService
     *
     * @throws RuntimeException If no service class configured
     */
    protected function getServiceClass()
    {
        if ($this->getOption('object')) {
            return $this->getOption('object');
        }

        if ($this->getOption('class')) {
            [$class, $args] = $this->getOption('class');

            return match (count($args)) {
                0 => new $class(),
                1 => new $class($args[0]),
                2 => new $class($args[0], $args[1]),
                default => new $class(...$args),
            };
        }

        throw new RuntimeException('No service class configured', 500);
    }

    /**
     * @param string $method
     * 
     * @return MethodDefinition
     *
     * @throws InvalidArgumentException If method not found
     */
    protected function getMethodDefinition($method)
    {
        $definition = $this->getDefinition()->getMethod($method);

        if (!$definition) {
            throw new InvalidArgumentException('Method not found', 404);
        }

        return $definition;
    }

    private function getErrorStacker(): ?ErrorStacker
    {
        if (!class_exists(ErrorStacker::class)) {
            return null;
        }

        if ($this->errorStacker === null) {
            $this->errorStacker = new ErrorStacker([
                'displayError' => false,
                'throwException' => true,
                'provider' => function($errstr, $errno) {
                    return $this->fault($errstr, $errno);
                }
            ]);
        }

        return $this->errorStacker;
    }
}
