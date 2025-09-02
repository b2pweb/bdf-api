<?php

namespace Bdf\Api\Server\Events;

use Symfony\Contracts\EventDispatcher\Event;
use Throwable;

/**
 * Trigger when an exception is thrown by the service
 */
class ServiceExceptionEvent extends Event
{
    /**
     * @var Throwable|null
     */
    private $exception;

    /**
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Change the exception
     *
     * @param Throwable|null $exception
     */
    public function setException(?Throwable $exception): void
    {
        $this->exception = $exception;
    }

    /**
     * @return Throwable|null
     */
    public function exception(): ?Throwable
    {
        return $this->exception;
    }
}
