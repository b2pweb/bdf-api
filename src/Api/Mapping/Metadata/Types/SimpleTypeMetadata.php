<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ContextInterface;

/**
 * Class SimpleTypeMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class SimpleTypeMetadata implements TypeMetadataInterface
{
    /**
     * @var string
     */
    protected $name;


    /**
     * SimpleTypeMetadata constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function map($source, ContextInterface $context)
    {
        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function unmap($source, ContextInterface $context)
    {
        return $source;
    }
}
