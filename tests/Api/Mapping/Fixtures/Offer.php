<?php

namespace Bdf\Api\Mapping\Fixtures;

/**
 * Class Offer
 *
 * @package Bdf\Api\Mapping\Fixtures
 */
class Offer
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Location
     */
    protected $location;


    /**
     * Offer constructor.
     *
     * @param string $name
     * @param Location $location
     */
    public function __construct($name, Location $location)
    {
        $this->name = $name;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }
}
