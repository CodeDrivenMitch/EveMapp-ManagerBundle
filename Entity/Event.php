<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Event
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="description", type="string", length=255)
	 */
	private $description;

	/**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="WebUser")
     * @ORM\JoinColumn(name="owner", referencedColumnName="id")
     */
    private $owner;

	/**
	 * @var integer
	 *
	 * @ORM\OneToOne(targetEntity="Image")
	 * @ORM\JoinColumn(name="image", referencedColumnName="id")
	 */
	private $image;

	/**
	 * @var integer
	 *
	 * @ORM\OneToOne(targetEntity="EventBounds")
	 * @ORM\JoinColumn(name="bounds", referencedColumnName="id")
	 */
	private $bounds;


	protected $eventBounds;


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
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Event
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime 
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set owner
     *
     * @param integer $owner
     * @return Event
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return integer 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set eventBounds
     *
     * @param Eventbounds $eventBounds
     * @return Event
     */
    public function setEventBounds($eventBounds)
    {
        $this->eventBounds = $eventBounds;

        return $this;
    }

    /**
     * Get eventBounds
     *
     * @return Eventbounds
     */
    public function getEventBounds()
    {
        return $this->eventBounds;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
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
     * Set image
     *
     * @param Image $image
     * @return Event
     */
    public function setImage(Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set bounds
     *
     * @param EventBounds $bounds
     * @return Event
     */
    public function setBounds(EventBounds $bounds)
    {
        $this->bounds = $bounds;

        return $this;
    }

    /**
     * Get bounds
     *
     * @return EventBounds
     */
    public function getBounds()
    {
        return $this->bounds;
    }
}
