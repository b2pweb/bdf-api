<?php

namespace Bdf\Api\Mapping\Loader;

use Bdf\Api\Mapping\Builder\ServiceBuilder;

/**
 * Class PhpFileLoader
 *
 * @package Bdf\Api\Mapping\Loader
 */
class PhpFileLoader extends AbstractFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $builder = new ServiceBuilder($this->registry);

        include $this->file;

        return $builder->build();
    }
}
