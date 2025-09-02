<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ContextInterface;

/**
 * Class OptionTypeMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class OptionTypeMetadata implements TypeMetadataInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'options';
    }

    /**
     * {@inheritdoc}
     */
    public function map($source, ContextInterface $context)
    {
        $result = [];

        if (!is_array($source)) {
            return $result;
        }

        foreach ($source as $option) {
            $result[$option->key] = $option->value;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function unmap($source, ContextInterface $context)
    {
        if ($source === null) {
            return [];
        }

        $result = [];

        foreach ($source as $key => $value) {
            $result[] = (object) [
                'key'   => $key,
                'value' => $value
            ];
        }

        return $result;
    }

}
