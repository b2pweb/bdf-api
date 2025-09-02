<?php

namespace Bdf\Api\Providers;

use Bdf\Api\Listeners\LoggerListener;
use Bdf\Log\LogServiceProvider as BaseLogServiceProvider;
use Bdf\Web\Application;
use Bdf\Web\Providers\BootableProviderInterface;

/**
 * LogServiceProvider
 */
class LogServiceProvider extends BaseLogServiceProvider implements BootableProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->subscribe(new LoggerListener($app->get('logger')));
    }
}
