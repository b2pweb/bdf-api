<?php

namespace Bdf\Api\Mapping\Transformers;

use Bdf\Api\Mapping\ContextInterface;

/**
 * TransformerInterface
 *
 * @package Bdf\Api\Mapping\Transformers
 */
interface TransformerInterface
{
    /**
     * Transform a api value to a domain value
     *
     * @param mixed $value
     * @param ContextInterface $context
     *
     * @return mixed
     */
    public function doTransform($value, ContextInterface $context);

    /**
     * Transform a domain value to a api value
     *
     * @param mixed $value
     * @param ContextInterface $context
     *
     * @return mixed
     */
    public function undoTransform($value, ContextInterface $context);
}
