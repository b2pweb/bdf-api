<?php

namespace Bdf\Api\Definition\Loader\Fixtures\Types;

/**
 * Class Offer
 *
 * @api-class Offer
 *
 * @package Bdf\Api\Definition\Loader\Fixtures\Types
 */
class Offer
{
    /**
     * @api-var string
     */
    public $name;

    /**
     * @api-var string
     */
    public $city;

    /**
     * @api-var string
     */
    public $country = 'FR';

    /**
     * @api-optional
     *
     * @api-var int
     */
    public $nbAssociatedSearches;

    /**
     * @api-ignore
     */
    public $testIgnore;
}
