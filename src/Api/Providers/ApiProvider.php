<?php

namespace Bdf\Api\Providers;

use Bdf\Routing\Listeners\RouterListener;
use Bdf\Web\Application;
use Bdf\Web\Providers\BootableProviderInterface;
use Bdf\Web\Providers\ServiceProviderInterface;

/**
 * ApiProvider
 */
class ApiProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function configure(Application $app)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
//        $app->subscribe($app->get(RouterListener::class));
        $app->subscribe($app->get('routerListener'));
    }
}
