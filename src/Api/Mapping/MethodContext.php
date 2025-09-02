<?php

namespace Bdf\Api\Mapping;

use Bdf\Api\Mapping\Metadata\MethodMetadata;

/**
 * Class MethodContext
 *
 * @package Bdf\Api\Mapping
 */
class MethodContext extends AbstractContextWrapper
{
    /**
     * @var MethodMetadata
     */
    protected $metadata;

    /**
     * @var array
     */
    protected $source;

    /**
     * @var array
     */
    protected $parameters = [];


    /**
     * MethodContext constructor.
     *
     * @param ContextInterface $wrappedContext
     * @param MethodMetadata $metadata
     * @param array $source
     */
    public function __construct(ContextInterface $wrappedContext, MethodMetadata $metadata, array $source)
    {
        parent::__construct($wrappedContext);

        $this->metadata = $metadata;
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if ($this->has($name)) {
            return $this->parameters[$name];
        }

        return $this->metadata->mapParameterByName($name, $this->source, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        $this->loadOptions();

        return parent::getOption($name, $default);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $this->loadOptions();

        return parent::getOptions();
    }

    /**
     *
     */
    protected function loadOptions()
    {
        if ($this->metadata->getParameterOption() && !$this->has($this->metadata->getParameterOption())) {
            $this->wrappedContext->setOptions($this->get($this->metadata->getParameterOption()));
        }
    }
}
