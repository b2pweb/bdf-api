<?php

namespace Bdf\Api\Definition\Loader\Fixtures;

use Bdf\Api\ApiService;
use Bdf\Api\Definition\Loader\Fixtures\Types\Offer;

/**
 * Class TestService
 *
 * @package Bdf\Api\Definition\Loader\Fixtures
 *
 * @api-name Hello
 */
class TestService extends ApiService
{
    /**
     * Ma description qui décrit comment fonctionne cette méthode de l'api.
     *
     * @api-action
     *
     * @api-param Offer $offer
     * @api-param boolean $optionalParameter
     *
     * @api-return string
     *
     * @param Offer $offer
     *
     * @return string
     */
    public function method1(Offer $offer, $optionalParameter = true)
    {
        return $offer->getName();
    }

    /**
     *
     */
    public function method2()
    {
    }

    /**
     * @api-action
     */
    public function methodWithoutParameterAndReturn()
    {
    }

    /**
     * @api-action
     *
     * @api-param string $name The parameter name
     */
    public function methodWithDescriptionParameter($name)
    {
    }

    /**
     * @api-action
     *
     * @throws \Exception
     */
    public function methodThrowingException()
    {
    }

    /**
     * @api-action
     */
    public static function methodStatic1()
    {
    }
}