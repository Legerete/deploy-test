<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Legerete\User\Model\Entity\UserEntity as BaseUserEntity;

/**
 * @ORM\Entity
 * @Table(name="acl_role")
 * @ORM\HasLifecycleCallbacks
 */
class UserEntity extends BaseUserEntity
{

	/**
	 * @ORM\ManyToMany(targetEntity="RoleEntity")
	 * @ORM\JoinTable(name="acl_user_role",
	 * 		joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", unique=true)}
	 *		)
	 */
	protected $roles;

	/**
	 * ************************************* Getters ***************************************
	 */


	/**
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles;
	}


	/**
	 * ************************************* Setters ***************************************
	 */

}
