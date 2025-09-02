<?php

namespace Bdf\Api\Listeners;

use Bdf\Api\ApiRequest;
use Bdf\Api\Events\ApiEvent;
use Bdf\Api\Events\ProtocolEvent;
use Bdf\Api\Events\KernelEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

use function method_exists;

/**
 * LoggerListener
 */
class LoggerListener implements EventSubscriberInterface
{
    protected LoggerInterface $logger;
    private bool $enableLog = true;

    /**
     * Log context
     * 
     * @var array
     */
    protected array $context = [];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        if (method_exists($logger, 'addProcessor')) {
            $this->logger->addProcessor(function($record) {
                $record['extra'] = $this->context + $record['extra'];
                return $record;
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::API       => 'onKernelApi',
            KernelEvents::PROTOCOL  => [['onKernelProtocol', 15]],
            KernelEvents::RESPONSE  => 'onKernelResponse',
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @param ApiEvent $event
     */
    public function onKernelApi(ApiEvent $event): void
    {
        $request = $event->getRequest();
        
        if ($request instanceof ApiRequest) {
            $this->context = [
                'apiName'     => $request->getApiName(),
                'clientIp'    => $request->getClientIp(),
                'environment' => $request->getEnvironment(),
                'keyOwner'    => $request->getApiKey()->getOwnerName(),
                'protocol'    => $request->getApiProtocol(),
                'threadId'    => $request->getThreadId(),
                'version'     => $request->getApiVersion(),
            ];
        }
        
//        $this->logger->debug($event->getRequest()->getContent(), ['type' => 'request raw']);
    }

    /**
     * @param ProtocolEvent $event
     */
    public function onKernelProtocol(ProtocolEvent $event): void
    {
        $request = $event->getRequest();
        
        $this->enableLog = !$event->getProtocol()->isServiceMapRequest($request);
        
        if ($this->enableLog) {
            $this->logger->debug($request->getRequestUri()."\r\n".$request->headers, ['type' => 'header'] + ['extra' => $this->context]);
            $this->logger->debug($request->getContent() ?: '[no body]', ['type' => 'request'] + ['extra' => $this->context]);
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->enableLog) {
            $this->logger->debug($event->getResponse()->getContent() ?: '[no body]', ['type' => 'response'] + ['extra' => $this->context]);
        }
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->enableLog) {
            $exception = $event->getThrowable();

            $this->logger->error('(' . $exception->getCode() . ')' . $exception->getMessage(), ['exception' => $exception] + ['extra' => $this->context]);
        }
    }
}
