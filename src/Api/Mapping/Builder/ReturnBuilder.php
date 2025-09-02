<?php

namespace Bdf\Api\Mapping\Builder;

use Bdf\Api\Mapping\Metadata\ReturnMetadata;
use Bdf\Api\Mapping\Registry;

/**
 * Class ReturnBuilder
 *
 * @package Bdf\Api\Mapping\Builder
 */
class ReturnBuilder
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $transformer;


    /**
     * ReturnBuilder constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry = null)
    {
        $this->registry = $registry ?: new Registry();
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param mixed $transformer
     *
     * @return $this
     */
    public function transformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @param ServiceBuilder $serviceBuilder
     *
     * @return ReturnMetadata
     */
    public function build(ServiceBuilder $serviceBuilder)
    {
        $metadata = new ReturnMetadata($serviceBuilder->buildType($this->type));

        if ($this->transformer) {
            $metadata->setTransformer($this->registry->getTransformer($this->transformer));
        }

        return $metadata;
    }
}