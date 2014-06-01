<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/23/14
 * Time: 9:47 AM
 */

namespace EveMapp\ManagerBundle\Entity;


use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * EveMapp\ManagerBundle\Entity\Role
 * @ORM\Table(name="webusers_role")
 * @ORM\Entity()
 */
class Role implements RoleInterface, \Serializable
{
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id()
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="name", type="string", length=30)
	 */
	private $name;

	/**
	 * @ORM\Column(name="role", type="string", length=20, unique=true)
	 */
	private $role;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="EveMapp\ManagerBundle\Entity\WebUser", mappedBy="roles")
	 */
	private $users;

	public function __construct()
	{
		$this->users = new ArrayCollection();
	}

	/**
	 * @see RoleInterface
	 */
	public function getRole()
	{
		return $this->role;
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
     * Set name
     *
     * @param string $name
     * @return Role
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
     * Set role
     *
     * @param string $role
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Add users
     *
     * @param \EveMapp\ManagerBundle\Entity\WebUser $users
     * @return Role
     */
    public function addUser(\EveMapp\ManagerBundle\Entity\WebUser $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \EveMapp\ManagerBundle\Entity\WebUser $users
     */
    public function removeUser(\EveMapp\ManagerBundle\Entity\WebUser $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }

	/**
	 * @see \Serializable::serialize()
	 */
	public function serialize()
	{
		/*
		 * ! Don't serialize $users field !
		 */
		return \serialize(array(
			$this->id,
			$this->role
		));
	}

	/**
	 * @see \Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		list(
			$this->id,
			$this->role
			) = \unserialize($serialized);
	}
}
