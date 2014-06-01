<?php

namespace EveMapp\ManagerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use EveMapp\ManagerBundle\Entity\Role;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Acme\UserBundle\Entity\User
 *
 * @ORM\Table(name="web_users")
 * @ORM\Entity(repositoryClass="EveMapp\ManagerBundle\Entity\WebUserRepository")
 */
class WebUser implements AdvancedUserInterface, EquatableInterface, \Serializable
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=25, unique=true)
	 */
	private $username;

	/**
	 * @ORM\Column(type="string", length=64)
	 */
	private $password;

	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(name="is_active", type="boolean")
	 */
	private $isActive;

	public $plainPassword;

	/**
	 * @inheritDoc
	 */
	public function getUsername()
	{
		return $this->username;
	}


	/**
	 * @inheritDoc
	 */
	public function getPassword()
	{
		return $this->password;
	}

	public function setPassword($pass) {
		$this->password = $pass;
	}

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
	 *
	 */
	private $roles;



	/**
	 * @inheritDoc
	 */
	public function eraseCredentials()
	{
	}

	/**
	 * Serializes the content of the current User object
	 * @return string
	 */
	public function serialize()
	{
		/*
		 * ! Don't serialize $roles field !
		 */
		return \serialize(array(
			$this->id,
			$this->username,
			$this->email,
			$this->password,
			$this->isActive
		));
	}

	/**
	 * @see \Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		list (
			$this->id,
			$this->username,
			$this->email,
			$this->password,
			$this->isActive
			) = \unserialize($serialized);
	}

	public function isEnabled()
	{
		return $this->isActive;
	}

	public function getEmail() {
		return $this->email;
	}

	public function setEmail($email) {
		$this->email = $email;
	}

	public function setUsername($username) {
		$this->username = $username;
	}


	/**
	 * Returns the roles granted to the user.
	 *
	 * <code>
	 * public function getRoles()
	 * {
	 *     return array('ROLE_USER');
	 * }
	 * </code>
	 *
	 * Alternatively, the roles might be stored on a ``roles`` property,
	 * and populated in any number of different ways when the user object
	 * is created.
	 *
	 * @return Role[] The user roles
	 */
	public function getRoles()
	{
		return $this->roles->toArray();
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return WebUser
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Add roles
     *
     * @param Role $roles
     * @return WebUser
     */
    public function addRole(Role $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param Role $roles
     */
    public function removeRole(Role $roles)
    {
        $this->roles->removeElement($roles);
    }

	/**
	 * Returns the salt that was originally used to encode the password.
	 *
	 * This can return null if the password was not encoded using a salt.
	 *
	 * @return string|null The salt
	 */
	public function getSalt()
	{
		// TODO: Implement getSalt() method.
	}
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    }

	/**
	 * Checks whether the user's account has expired.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw an AccountExpiredException and prevent login.
	 *
	 * @return bool    true if the user's account is non expired, false otherwise
	 *
	 * @see AccountExpiredException
	 */
	public function isAccountNonExpired()
	{
		return true;
	}

	/**
	 * Checks whether the user is locked.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw a LockedException and prevent login.
	 *
	 * @return bool    true if the user is not locked, false otherwise
	 *
	 * @see LockedException
	 */
	public function isAccountNonLocked()
	{
		return true;
	}

	/**
	 * Checks whether the user's credentials (password) has expired.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw a CredentialsExpiredException and prevent login.
	 *
	 * @return bool    true if the user's credentials are non expired, false otherwise
	 *
	 * @see CredentialsExpiredException
	 */
	public function isCredentialsNonExpired()
	{
		return true;
	}

	/**
	 * The equality comparison should neither be done by referential equality
	 * nor by comparing identities (i.e. getId() === getId()).
	 *
	 * However, you do not need to compare every attribute, but only those that
	 * are relevant for assessing whether re-authentication is required.
	 *
	 * Also implementation should consider that $user instance may implement
	 * the extended user interface `AdvancedUserInterface`.
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function isEqualTo(UserInterface $user)
	{
		// TODO: Implement isEqualTo() method.
	}
}
