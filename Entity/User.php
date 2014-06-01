<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class User
{
	/**
	 * @var string
	 *
	 * @ORM\Column(name="id", type="string", length=100)
	 * @ORM\Id
	 */
	protected $id;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="last_registered", type="datetime")
	 */
	private $lastRegistered;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="first_registered", type="datetime")
	 */
	private $firstRegistered;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="privacysetting", type="string", length=10)
	 */
	private $privacysetting;

	/**
	 * @var array
	 *
	 * @ORM\Column(name="friends", type="array")
	 */
	private $friends;

	/**
	 * @ORM\OneToMany(targetEntity="LocationEntry", mappedBy="userId")
	 */
	protected $entries;

	public function __construct()
	{
		$this->entries = new ArrayCollection();
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
	 * Set lastRegistered
	 *
	 * @param \DateTime $lastRegistered
	 * @return User
	 */
	public function setLastRegistered($lastRegistered)
	{
		$this->lastRegistered = $lastRegistered;

		return $this;
	}

	/**
	 * Get lastRegistered
	 *
	 * @return \DateTime
	 */
	public function getLastRegistered()
	{
		return $this->lastRegistered;
	}

	/**
	 * Set firstRegistered
	 *
	 * @param \DateTime $firstRegistered
	 * @return User
	 */
	public function setFirstRegistered($firstRegistered)
	{
		$this->firstRegistered = $firstRegistered;

		return $this;
	}

	/**
	 * Get firstRegistered
	 *
	 * @return \DateTime
	 */
	public function getFirstRegistered()
	{
		return $this->firstRegistered;
	}

	/**
	 * Set privacysetting
	 *
	 * @param string $privacysetting
	 * @return User
	 */
	public function setPrivacysetting($privacysetting)
	{
		$this->privacysetting = $privacysetting;

		return $this;
	}

	/**
	 * Get privacysetting
	 *
	 * @return string
	 */
	public function getPrivacysetting()
	{
		return $this->privacysetting;
	}

	public function setId($id)
	{
		$this->id = $id;
	}


	public function setFriends($friends)
	{
		$this->friends = $friends;
	}

	public function getFriends()
	{
		return $this->friends;
	}

    /**
     * Add entries
     *
     * @param \EveMapp\ManagerBundle\Entity\LocationEntry $entries
     * @return User
     */
    public function addEntry(\EveMapp\ManagerBundle\Entity\LocationEntry $entries)
    {
        $this->entries[] = $entries;

        return $this;
    }

    /**
     * Remove entries
     *
     * @param \EveMapp\ManagerBundle\Entity\LocationEntry $entries
     */
    public function removeEntry(\EveMapp\ManagerBundle\Entity\LocationEntry $entries)
    {
        $this->entries->removeElement($entries);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntries()
    {
        return $this->entries;
    }
}
