<?php

namespace Bdf\Api\Definition\Loader;

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
     * AbstractFileLoader constructor.
     *
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }
}
