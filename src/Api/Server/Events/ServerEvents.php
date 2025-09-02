<?php

namespace Bdf\Api\Server\Events;

/**
 * Class ServerEvents
 *
 * @package Bdf\Api\Server\Events
 */
class ServerEvents
{
    /**
     * The Pre Service event occurs before the call of the service method
     */
    const PRE_SERVICE = 'server.pre_service';

    /**
     * The Pre Service event occurs after the call of the service method
     */
    const POST_SERVICE = 'server.post_service';
}
