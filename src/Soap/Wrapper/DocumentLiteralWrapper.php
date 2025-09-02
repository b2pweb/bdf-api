<?php

namespace Bdf\Soap\Wrapper;

/**
 * Class DocumentLiteralWrapper
 *
 * @package Bdf\Soap\Wrapper
 */
class DocumentLiteralWrapper extends DefaultWrapper
{
    /**
     * {@inheritDoc}
     */
    protected function _unserialize($var)
    {
        if (is_scalar($var) || is_resource($var) || is_null($var)) {
            return $var;
        }

        if ($var instanceof \stdClass) {
            $properties = get_object_vars($var);

            if (isset($properties['item']) && count($properties) == 1) {
                if (is_array($properties['item'])) {
                    $var = $properties['item'];
                } elseif (is_object($properties['item'])) {
                    $var = [$properties['item']];
                }
            }
        }

        foreach ($var as &$value) {
            $value = $this->_unserialize($value);
        }

        return $var;
    }

    /**
     * {@inheritDoc}
     */
    protected function _processMethod($name, $arguments = [])
    {
        $this->_assertOnlyOneArgument($arguments);

        return [
            $name . 'Result' => parent::_processMethod($name, get_object_vars($arguments[0]))
        ];
    }

    /**
     * @param array $args
     * 
     * @throws \SoapFault
     */
    protected function _assertOnlyOneArgument(array $args): void
    {
        if (count($args) != 1) {
            throw new \SoapFault('Sender', sprintf(
                "Expecting exactly one argument that is the document/literal wrapper, got %d", count($args)
            ));
        }
    }
}
