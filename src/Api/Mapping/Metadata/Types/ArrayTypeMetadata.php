<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ContextInterface;

/**
 * Class ArrayTypeMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class ArrayTypeMetadata implements TypeMetadataInterface
{
    /**
     * @var TypeMetadataInterface
     */
    protected $internalType;


    /**
     * ArrayTypeMetadata constructor.
     *
     * @param TypeMetadataInterface $internalType
     */
    public function __construct(TypeMetadataInterface $internalType)
    {
        $this->internalType = $internalType;
    }

    /**
     * @return TypeMetadataInterface
     */
    public function getInternalType()
    {
        return $this->internalType;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->internalType->getName() . '[]';
    }

    /**
     * {@inheritdoc}
     */
    public function map($source, ContextInterface $context)
    {
        $target = [];

        if (!is_array($source)) {
            return $target;
        }

        foreach ($source as $key => $value) {
            $target[$key] = $this->internalType->map($value, $context);
        }

        return $target;
    }

    /**
     * {@inheritdoc}
     */
    public function unmap($source, ContextInterface $context)
    {
        if ($source === null) {
            return null;
        }

        $target = [];

        foreach ($source as $key => $value) {
            $target[$key] = $this->internalType->unmap($value, $context);
        }

        return $target;
    }
}
