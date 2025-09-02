<?php

namespace Bdf\Api\Definition\Loader;

use Bdf\Api\Definition\ServiceDefinition;

/**
 * Interface LoaderInterface
 *
 * @package Bdf\Api\Definition\Loader
 */
interface LoaderInterface
{
    /**
     * @return ServiceDefinition
     */
    public function load();
}
