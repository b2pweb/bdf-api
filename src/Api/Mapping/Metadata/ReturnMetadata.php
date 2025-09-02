<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Mapping\ContextInterface;
use Bdf\Api\Mapping\Metadata\Types\TypeMetadataInterface;
use Bdf\Api\Mapping\Transformers\TransformerInterface;

/**
 * Class ReturnMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class ReturnMetadata
{
    /**
     * @var TypeMetadataInterface
     */
    protected $type;

    /**
     * @var TransformerInterface
     */
    protected $transformer;


    /**
     * ReturnMetadata constructor.
     *
     * @param TypeMetadataInterface $type
     */
    public function __construct(TypeMetadataInterface $type)
    {
        $this->type = $type;
    }

    /**
     * @return TypeMetadataInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param TypeMetadataInterface $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return TransformerInterface
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param TransformerInterface $transformer
     *
     * @return $this
     */
    public function setTransformer(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * @param mixed $source
     * @param ContextInterface $context
     *
     * @return mixed
     */
    public function map($source, ContextInterface $context)
    {
        return $source;
    }

    /**
     * @param mixed $source
     * @param ContextInterface $context
     *
     * @return mixed
     */
    public function unmap($source, ContextInterface $context)
    {
        // Permet de ne pas mapper la valeur null
        if ($source === null) {
            return null;
        }

        if ($this->transformer) {
            $source = $this->transformer->undoTransform($source, $context);
        }

        return $this->type->unmap($source, $context);
    }
}
