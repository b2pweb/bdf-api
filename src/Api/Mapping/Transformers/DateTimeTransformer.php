<?php

namespace Bdf\Api\Mapping\Transformers;

use Bdf\Api\Exception\InvalidArgumentException;
use Bdf\Api\Mapping\ContextInterface;
use DateTime;
use DateTimeZone;

/**
 * Class DateTimeTransformer
 *
 * @package Bdf\Api\Mapping\Transformers
 */
class DateTimeTransformer implements TransformerInterface
{
    /**
     * @var string
     */
    protected $defaultTimezone;

    /**
     * @var string
     */
    protected $defaultFormat;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var array
     */
    protected $allowedOptions = [];


    /**
     * DateTimeTransformer constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->defaultTimezone = isset($options['timezone']) ? $options['timezone'] : date_default_timezone_get();
        $this->defaultFormat = isset($options['format']) ? $options['format'] : 'Y/m/d H:i';

        if (isset($options['errorMessage'])) {
            $this->errorMessage = $options['errorMessage'];
        }

        if (isset($options['allowedOptions'])) {
            $this->allowedOptions = $options['allowedOptions'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function doTransform($value, ContextInterface $context)
    {
        if ($value === null) {
            return null;
        }
        
        $result = DateTime::createFromFormat(
            $this->getOption('format', $context, $this->defaultFormat),
            $value,
            new DateTimeZone($this->getOption('timezone', $context, $this->defaultTimezone))
        );

        if (!$result) {
            throw new InvalidArgumentException(
                $this->errorMessage ?: sprintf('DateTime value does not match the expected format : %s', $this->getOption('format', $context, $this->defaultFormat))
            );
        }

        $result->setTimezone(new DateTimeZone($this->defaultTimezone));

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function undoTransform($value, ContextInterface $context)
    {
        if ($value === null) {
            return null;
        }

        /** @var DateTime $value */
        $value->setTimezone(new DateTimeZone($this->getOption('timezone', $context, $this->defaultTimezone)));

        return $value->format($this->getOption('format', $context, $this->defaultFormat));
    }

    /**
     * @param string $name
     * @param ContextInterface $context
     * @param mixed $default
     *
     * @return mixed
     */
    protected function getOption($name, ContextInterface $context, $default = null)
    {
        if (empty($this->allowedOptions[$name])) {
            return $default;
        }

        return $context->getOption($this->allowedOptions[$name], $default);
    }
}
