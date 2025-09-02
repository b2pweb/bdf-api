<?php

namespace Bdf\Api\Definition\Loader;

use Psr\SimpleCache\CacheInterface;

/**
 * CacheFileLoader
 */
class CacheLoader implements LoaderInterface
{
    /**
     * @var string
     */
    private $serviceName;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var CacheInterface
     */
    private $cache;


    /**
     * CacheLoader constructor.
     *
     * @param string $serviceName
     * @param LoaderInterface $loader
     * @param CacheInterface $cache
     */
    public function __construct($serviceName, LoaderInterface $loader, CacheInterface $cache)
    {
        $this->serviceName = $serviceName;
        $this->loader = $loader;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $definition = $this->cache->get($this->serviceName);

        if (!$definition) {
            $this->cache->set($this->serviceName, $definition = $this->loader->load());
        }

        return $definition;
    }

}
