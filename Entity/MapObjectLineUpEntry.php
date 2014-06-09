<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MapObjectLineUpEntry
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MapObjectLineUpEntry
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
     * @var string
     *
     * @ORM\Column(name="performer", type="string", length=255)
     */
    private $performer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startTime", type="datetime")
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endTime", type="datetime")
     */
    private $endTime;

	/**
	 * @ORM\ManyToOne(targetEntity="MapObject", inversedBy="lineUpEntries", cascade={"persist"})
	 */
	private $mapObject;


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
     * Set performer
     *
     * @param string $performer
     * @return MapObjectLineUpEntry
     */
    public function setPerformer($performer)
    {
        $this->performer = $performer;

        return $this;
    }

    /**
     * Get performer
     *
     * @return string 
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     * @return MapObjectLineUpEntry
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime 
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     * @return MapObjectLineUpEntry
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime 
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set mapObject
     *
     * @param \EveMapp\ManagerBundle\Entity\MapObject $mapObject
     * @return MapObjectLineUpEntry
     */
    public function setMapObject(\EveMapp\ManagerBundle\Entity\MapObject $mapObject = null)
    {
        $this->mapObject = $mapObject;

        return $this;
    }

    /**
     * Get mapObject
     *
     * @return \EveMapp\ManagerBundle\Entity\MapObject 
     */
    public function getMapObject()
    {
        return $this->mapObject;
    }
}
