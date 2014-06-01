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
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="EveMapp\ManagerBundle\Entity\Event")
     * @ORM\JoinColumn(name="Event", referencedColumnName="id")
     * @ORM\Column(name="event_id", type="integer")
     */
    private $eventId;

    /**
     * @var float
     *
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
     * Set eventId
     *
     * @param integer $eventId
     * @return EventBounds
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return integer 
     */
    public function getEventId()
    {
        return $this->eventId;
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
	 * @return integer zoom
	 */
	public function getZoom()
	{
		return $this->zoom;
	}

	/**
	 * @param integer $zoom
	 */
	public function setZoom($zoom)
	{
		$this->zoom = $zoom;
	}
}
