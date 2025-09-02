<?php

namespace Bdf\Api\Mapping\Metadata;

use Bdf\Api\Exception\InvalidArgumentException;
use Bdf\Api\Mapping\Constraints\ConstraintInterface;
use Bdf\Api\Mapping\Context;
use Bdf\Api\Mapping\MethodContext;

/**
 * Class MethodMetadata
 *
 * @package Bdf\Api\Mapping\Metadata
 */
class MethodMetadata
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ParameterMetadata[]
     */
    protected $parameters = [];

    /**
     * @var ReturnMetadata
     */
    protected $return;

    /**
     * @var string
     */
    protected $parameterOption;

    /**
     * @var ConstraintInterface[]
     */
    protected $constraints = [];


    /**
     * MethodMetadata constructor.
     *
     * @param string $name
     * @param ParameterMetadata[] $parameters
     */
    public function __construct($name, array $parameters = [])
    {
        $this->name = $name;

        $this->setParameters($parameters);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ParameterMetadata[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param ParameterMetadata[] $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = [];

        foreach ($parameters as $parameter) {
            $this->addParameter($parameter);
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return ParameterMetadata
     */
    public function getParameter($name)
    {
        return $this->parameters[$name];
    }

    /**
     * @param ParameterMetadata $parameter
     *
     * @return $this
     */
    public function addParameter(ParameterMetadata $parameter)
    {
        $this->parameters[$parameter->getName()] = $parameter;

        return $this;
    }

    /**
     * @return ReturnMetadata
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param ReturnMetadata $return
     *
     * @return $this
     */
    public function setReturn(ReturnMetadata $return)
    {
        $this->return = $return;

        return $this;
    }

    /**
     * @return string
     */
    public function getParameterOption()
    {
        return $this->parameterOption;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setParameterOption($name)
    {
        $this->parameterOption = $name;

        return $this;
    }

    /**
     * @param ConstraintInterface $constraint
     *
     * @return $this
     */
    public function addConstraint(ConstraintInterface $constraint)
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param array $source
     * @param Context $context
     *
     * @return array
     */
    public function map(array $source, Context $context)
    {
        $context = new MethodContext($context, $this, $source);

        $result = [];

        foreach ($this->parameters as $parameter) {
            $result[$parameter->getName()] = $this->mapParameter($parameter, $source, $context);
        }

        foreach ($this->constraints as $constraint) {
            $constraint->validate($result, $context);
        }

        return $result;
    }

    /**
     * @param string $name
     * @param array $source
     * @param MethodContext $context
     *
     * @return mixed
     */
    public function mapParameterByName($name, array $source, MethodContext $context)
    {
        return $this->mapParameter($this->getParameter($name), $source, $context);
    }

    /**
     * @param ParameterMetadata $metadata
     * @param array $source
     * @param MethodContext $context
     *
     * @return mixed
     */
    public function mapParameter(ParameterMetadata $metadata, array $source, MethodContext $context)
    {
        if (!$context->has($metadata->getName())) {
            if (!array_key_exists($metadata->getName(), $source)) {
                throw new InvalidArgumentException('Parameter "' . $metadata->getName() . '" not found');
            }

            $context->set(
                $metadata->getName(),
                $metadata->map($source[$metadata->getName()], $context)
            );

            // Permet de redescendre les données du paramètre contenant les options sur la propriété adéquate du context
            if ($metadata->getName() === $this->parameterOption) {
                $context->setOptions($context->get($this->parameterOption));
            }
        }

        return $context->get($metadata->getName());
    }

    /**
     * @param mixed $source
     * @param Context $context
     *
     * @return mixed
     */
    public function unmap($source, Context $context)
    {
        if (!$this->return) {
            return null;
        }

        return $this->return->unmap($source, $context);
    }
}
