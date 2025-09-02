<?php

namespace Bdf\Api\Mapping\Loader;

use Bdf\Api\Mapping\Metadata\ServiceMetadata;

/**
 * Interface LoaderInterface
 *
 * @package Bdf\Api\Mapping\Loader
 */
interface LoaderInterface
{
    /**
     * @return ServiceMetadata
     */
    public function load();
}
