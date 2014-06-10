<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EveMapp\ManagerBundle\Entity\MapObjectLineUpEntry;
use EveMapp\ManagerBundle\Entity\MapObjectPrice;

/**
 * MapObject
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MapObject
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
     * @ORM\Column(name="event_id", type="integer")
     */
    private $eventId;

    /**
     * @var integer
     *
     * @ORM\Column(name="object_id", type="integer")
     */
    private $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="width", type="integer")
     */
    private $width;

    /**
     * @var integer
     *
     * @ORM\Column(name="height", type="integer")
     */
    private $height;

    /**
     * @var integer
     *
     * @ORM\Column(name="angle", type="integer")
     */
    private $angle;

    /**
     * @var float
     *
     * @ORM\Column(name="lat", type="float")
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="lng", type="float")
     */
    private $lng;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="url", type="string", length=255)
	 */
	private $url;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=255)
	 */
	private $description;

	/**
	 * Bidirectional - One-To-Many (INVERSE SIDE)
	 *
	 * @ORM\OneToMany(targetEntity="MapObjectPrice", mappedBy="mapObject", cascade={"persist"})
	 * @ORM\OrderBy({"price" = "ASC"})
	 */
	private $priceEntries;

	/**
	 * Bidirectional - One-To-Many (INVERSE SIDE)
	 *
	 * @ORM\OneToMany(targetEntity="MapObjectLineUpEntry", mappedBy="mapObject", cascade={"persist"})
	 * @ORM\OrderBy({"startTime" = "ASC"})
	 */
	private $lineUpEntries;


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
     * @return MapObject
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
     * Set objectId
     *
     * @param integer $objectId
     * @return MapObject
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return MapObject
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set width
     *
     * @param integer $width
     * @return MapObject
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     * @return MapObject
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set angle
     *
     * @param integer $angle
     * @return MapObject
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;

        return $this;
    }

    /**
     * Get angle
     *
     * @return integer 
     */
    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return MapObject
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * Get lat
     *
     * @return float 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return MapObject
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return MapObject
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return MapObject
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->priceEntries = new ArrayCollection();
    }

    /**
     * Add priceEntries
     *
     * @param MapObjectPrice $priceEntries
     * @return MapObject
     */
    public function addPriceEntry(MapObjectPrice $priceEntries)
    {
        $this->priceEntries[] = $priceEntries;

        return $this;
    }

    /**
     * Remove priceEntries
     *
     * @param MapObjectPrice $priceEntries
     */
    public function removePriceEntry(MapObjectPrice $priceEntries)
    {
        $this->priceEntries->removeElement($priceEntries);
    }

    /**
     * Get priceEntries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPriceEntries()
    {
        return $this->priceEntries;
    }

    /**
     * Add lineUpEntries
     *
     * @param MapObjectLineUpEntry $lineUpEntries
     * @return MapObject
     */
    public function addLineUpEntry(MapObjectLineUpEntry $lineUpEntries)
    {
        $this->lineUpEntries[] = $lineUpEntries;

        return $this;
    }

    /**
     * Remove lineUpEntries
     *
     * @param MapObjectLineUpEntry $lineUpEntries
     */
    public function removeLineUpEntry(MapObjectLineUpEntry $lineUpEntries)
    {
        $this->lineUpEntries->removeElement($lineUpEntries);
    }

    /**
     * Get lineUpEntries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLineUpEntries()
    {
        return $this->lineUpEntries;
    }
}
