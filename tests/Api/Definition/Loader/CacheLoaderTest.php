<?php

namespace Bdf\Api\Definition\Loader;

use Bdf\Api\Definition\ServiceDefinition;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;
use Psr\SimpleCache\CacheInterface;

/**
 * Class CacheLoaderTest
 *
 * @package Bdf\Api\Definition\Loader
 *
 * @group Bdf
 * @group Bdf_Api
 * @group Bdf_Api_Definition
 * @group Bdf_Api_Definition_Loader
 * @group Bdf_Api_Definition_Loader_CacheLoader
 *
 * @coversDefaultClass Bdf\Api\Definition\Loader\CacheLoader
 */
class CacheFileLoaderTest extends TestCase
{
    /**
     * @var LoaderInterface
     */
    protected $internalLoader;

    /**
     * @var CacheInterface
     */
    protected $cache;


    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->internalLoader = $this->createMock(LoaderInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
    }

    /**
     *
     */
    public function test_load()
    {
        $definition = new ServiceDefinition('Hello');

        $this->internalLoader->expects($this->once())->method('load')->will($this->returnValue($definition));

        $this->cache->expects($this->once())->method('get')
            ->with('Hello')
            ->will($this->returnValue(null))
        ;

        $this->cache->expects($this->once())->method('set')
            ->with('Hello', $definition)
        ;

        $this->assertEquals($definition, (new CacheLoader('Hello', $this->internalLoader, $this->cache))->load());
    }

    /**
     *
     */
    public function test_load_already_in_cache()
    {
        $definition = new ServiceDefinition('Hello');

        $this->internalLoader->expects($this->never())->method('load');

        $this->cache->expects($this->once())->method('get')
            ->with('Hello')
            ->will($this->returnValue($definition))
        ;

        $this->assertEquals($definition, (new CacheLoader('Hello', $this->internalLoader, $this->cache))->load());
    }
}
