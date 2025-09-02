<?php

namespace Bdf\Api\Definition\Loader\Fixtures;

/**
 * Ma description qui décrit comment fonctionne cette méthode de l'api.
 *
 * @api-name Hello
 *
 * @api-action
 *
 * @api-param Offer $offer
 * @api-param boolean $optionalParameter
 *
 * @api-return string
 *
 * @api-return string
 */
function method1($offer, $optionalParameter = true) {}

/**
 * @api-name Hello
 *
 * @api-action
 */
function methodWithoutParameterAndReturn() {}

/**
 * @api-name Hello
 *
 * @api-action
 *
 * @api-param string $name The parameter name
 */
function methodWithDescriptionParameter($name) {}

/**
 * @api-name Hello
 *
 * @api-action
 *
 * @throws \Exception
 */
function methodThrowingException() {}
