<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * MapObjectImage
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MapObjectImage
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
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

	/**
	 * @Assert\File(maxSize="6000000")
	 */
	private $file;
	private $temp;

	/**
	 * Sets file.
	 *
	 * @param UploadedFile $file
	 */
	public function setFile(UploadedFile $file = null)
	{
		$this->file = $file;
		// check if we have an old image path
		if (is_file($this->getAbsolutePath())) {
			// store the old name to delete after the update
			$this->temp = $this->getAbsolutePath();
		}
	}

	/**
	 * Get file.
	 *
	 * @return UploadedFile
	 */
	public function getFile()
	{
		return $this->file;
	}

	public function getAbsolutePath()
	{
		return $this->getUploadRootDir().'/'.$this->path;
	}

	public function getWebPath()
	{
		return null === $this->path
			? null
			: '/uploads/images/mapobjects/'.$this->getId().".".$this->getPath();
	}

	protected function getUploadRootDir()
	{
		// the absolute directory path where uploaded
		// documents should be saved
		return '/var/www/html/web/'.$this->getUploadDir();
	}

	protected function getUploadDir()
	{
		// get rid of the __DIR__ so it doesn't screw up
		// when displaying uploaded doc/image in the view.
		return '/uploads/images/mapobjects/';
	}

	/**
	 * @ORM\PostPersist()
	 * @ORM\PostUpdate()
	 */
	public function upload()
	{
		if (null === $this->getFile()) {
			return;
		}

		// check if we have an old image
		if (isset($this->temp)) {
			// delete the old image
			unlink($this->temp);
			// clear the temp image path
			$this->temp = null;
		}

		// you must throw an exception here if the file cannot be moved
		// so that the entity is not persisted to the database
		// which the UploadedFile move() method does
		$this->getFile()->move(
			$this->getUploadRootDir(),
			$this->id.'.'.$this->getFile()->guessExtension()
		);

		$this->setFile(null);
	}


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
     * @return MapObjectImage
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
     * Set type
     *
     * @param string $type
     * @return MapObjectImage
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
     * Set path
     *
     * @param string $path
     * @return MapObjectImage
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function preUpload()
	{
		if (null !== $this->getFile()) {
			$this->path = $this->getFile()->guessExtension();
		}
	}

	/**
	 * @ORM\PreRemove()
	 */
	public function storeFilenameForRemove()
	{
		$this->temp = $this->getAbsolutePath();
	}

	/**
	 * @ORM\PostRemove()
	 */
	public function removeUpload()
	{
		if (isset($this->temp)) {
			unlink($this->temp);
		}
	}
}
