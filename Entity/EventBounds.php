<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EventBounds
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EventBounds
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @Assert\NotNull(message="Center the map on your location!")
     * @ORM\Column(name="lat_low", type="float")
     */
    private $latLow;

    /**
     * @var float
     *
     * @ORM\Column(name="lat_high", type="float")
     */
    private $latHigh;

    /**
     * @var float
     *
     * @ORM\Column(name="lng_low", type="float")
     */
    private $lngLow;

    /**
     * @var float
     *
     * @ORM\Column(name="lng_high", type="float")
     */
    private $lngHigh;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="zoom", type="integer")
	 */
	private $zoom;

	/**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set latLow
     *
     * @param float $latLow
     * @return EventBounds
     */
    public function setLatLow($latLow)
    {
        $this->latLow = $latLow;

        return $this;
    }

    /**
     * Get latLow
     *
     * @return float 
     */
    public function getLatLow()
    {
        return $this->latLow;
    }

    /**
     * Set latHigh
     *
     * @param float $latHigh
     * @return EventBounds
     */
    public function setLatHigh($latHigh)
    {
        $this->latHigh = $latHigh;

        return $this;
    }

    /**
     * Get latHigh
     *
     * @return float 
     */
    public function getLatHigh()
    {
        return $this->latHigh;
    }

    /**
     * Set lngLow
     *
     * @param float $lngLow
     * @return EventBounds
     */
    public function setLngLow($lngLow)
    {
        $this->lngLow = $lngLow;

        return $this;
    }

    /**
     * Get lngLow
     *
     * @return float 
     */
    public function getLngLow()
    {
        return $this->lngLow;
    }

    /**
     * Set lngHigh
     *
     * @param float $lngHigh
     * @return EventBounds
     */
    public function setLngHigh($lngHigh)
    {
        $this->lngHigh = $lngHigh;

        return $this;
    }

    /**
     * Get lngHigh
     *
     * @return float 
     */
    public function getLngHigh()
    {
        return $this->lngHigh;
    }

    /**
     * Set zoom
     *
     * @param integer $zoom
     * @return EventBounds
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;

        return $this;
    }

    /**
     * Get zoom
     *
     * @return integer 
     */
    public function getZoom()
    {
        return $this->zoom;
    }
}
