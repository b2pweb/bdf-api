<?php

namespace Bdf\Api\Mapping\Metadata\Types;

use Bdf\Api\Mapping\ComplexTypeContext;

/**
 * Class DefaultPropertyMetadata
 *
 * @package Bdf\Api\Mapping\Metadata\Types
 */
class DefaultPropertyMetadata extends PropertyMetadata
{
    /**
     * @var bool
     */
    protected $nillable;

    /**
     * @var string
     */
    protected $definedBy;


    /**
     * @return bool
     */
    public function isNillable()
    {
        return $this->nillable;
    }

    /**
     * @param bool $nillable
     *
     * @return $this
     */
    public function setNillable($nillable)
    {
        $this->nillable = $nillable;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefinedBy()
    {
        return $this->definedBy ?: $this->name;
    }

    /**
     * @param string $definedBy
     *
     * @return $this
     */
    public function setDefinedBy($definedBy)
    {
        $this->definedBy = $definedBy;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function map($source, $target, ComplexTypeContext $context)
    {
        if (!$context->has($this->name)) {
            foreach (explode('.', $this->getDefinedBy()) as $part) {
                if (!isset($source->$part)) {
                    $source = null;

                    break;
                }

                $source = $source->$part;
            }

            $result = $this->type->map(
                $this->applyFilters($source, $context), $context
            );

            if ($this->transformer) {
                $result = $this->transformer->doTransform($result, $context);
            }

            $this->validateConstraints($result, $context);

            $context->set($this->name, $result);
        }

        $this->write($target, $context->get($this->name));
    }

    /**
     * {@inheritdoc}
     */
    public function unmap($source, $target, ComplexTypeContext $context)
    {
        $paths = explode('.', $this->getDefinedBy());

        for ($i = 0; $i < count($paths) - 1; $i++) {
            if (!isset($target->$paths[$i])) {
                $target->$paths[$i] = new \stdClass();
            }

            $target = $target->$paths[$i];
        }

        if (!$context->has($this->name)) {
            $result = $this->read($source);

            if ($this->transformer) {
                $result = $this->transformer->undoTransform($result, $context);
            }

            $context->set($this->name, $this->type->unmap($result, $context));
        }

        $target->{end($paths)} = $context->get($this->name);
    }
}
