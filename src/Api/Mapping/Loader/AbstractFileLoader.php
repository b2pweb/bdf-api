<?php

namespace Bdf\Api\Mapping\Loader;

use Bdf\Api\Mapping\Registry;

/**
 * AbstractFileLoader
 */
abstract class AbstractFileLoader implements LoaderInterface
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var Registry|null
     */
    protected $registry;


    /**
     * AbstractFileLoader constructor.
     *
     * @param string $file
     * @param Registry|null $registry
     */
    public function __construct($file, ?Registry $registry = null)
    {
        $this->file = $file;
        $this->registry = $registry;
    }
}
