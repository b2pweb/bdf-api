<?php

namespace Bdf\Api\Mapping\Fixtures;

/**
 * Class Location
 *
 * @package Bdf\Api\Mapping\Fixtures
 */
class Location
{
    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $country;


    /**
     * Location constructor.
     *
     * @param string $city
     * @param string $country
     */
    public function __construct($city, $country)
    {
        $this->city = $city;
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }
}
