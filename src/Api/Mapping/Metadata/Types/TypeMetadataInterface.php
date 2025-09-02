<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ContextInterface;

/**
 * Interface TypeMetadataInterface
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 */
interface TypeMetadataInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param mixed $source
     * @param ContextInterface $context
     *
     * @return mixed
     */
    public function map($source, ContextInterface $context);

    /**
     * @param mixed $source
     * @param ContextInterface $context
     *
     * @return mixed
     */
    public function unmap($source, ContextInterface $context);
}
