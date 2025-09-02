<?php

namespace Bdf\Api\Providers;

use Bdf\Api\Api;
use Bdf\Api\Application;

/**
 * ApiProviderInterface
 *
 * @method string path()
 * @see Application::register()
 */
interface ApiProviderInterface
{
    /**
     * Get the API path
     */
    //public function path(): string;

    /**
     * Configure a given api
     * 
     * <code>
     * $api->setName('test');
     * $api->setServiceClass('MyService');
     * </code>
     * 
     * @param Api $api
     * @param Application $app
     * 
     * @api
     */
    public function configureApi(Api $api, Application $app);
}
