<?php
/**
 * Created by PhpStorm.
 * User: mitchell
 * Date: 5/22/14
 * Time: 6:39 PM
 */

namespace EveMapp\ManagerBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;


use EveMapp\ManagerBundle\Entity\WebUser;

class Registration
{
	/**
	 * @Assert\Type(type="EveMapp\ManagerBundle\Entity\WebUser")
	 * @Assert\Valid()
	 */
	protected $user;

	public function setUser(WebUser $user)
	{
		$this->user = $user;
	}

	public function getUser()
	{
		return $this->user;
	}

}