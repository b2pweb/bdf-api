<?php

namespace Bdf\Api\Definition\Loader;

use Bdf\Api\Definition\Builder\ServiceDefinitionBuilder;

/**
 * Class PhpFileLoader
 *
 * @package Bdf\Api\Definition\Loader
 */
class PhpFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $builder = new ServiceDefinitionBuilder();

        include $this->file;

        return $builder->build();
    }
}
