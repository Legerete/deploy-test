<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Legerete\User\Model\SuperClass\UserSuperClass;

/**
 * @ORM\Entity
 * @Table(name="user")
 * @ORM\HasLifecycleCallbacks
 */
class UserEntity extends UserSuperClass
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
	 * @ORM\Column(type="boolean")
	 */
	protected $isAdmin = 0;

	/**
	 * UserEntity constructor.
	 *
	 * @param string $username
	 * @param string $name
	 * @param string $surname
	 * @param string $email
	 * @param ArrayCollection $roles
	 */
	public function __construct($username, $name, $surname, $email, ArrayCollection $roles)
	{
		parent::__construct($username, $name, $surname, $email);

		$this->roles = new ArrayCollection();

		$this->setRoles($roles);
	}

	/**
	 * ************************************* Getters ***************************************
	 */


	/**
	 * @return Collection
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * @return mixed
	 */
	public function getIsAdmin()
	{
		return $this->isAdmin;
	}

	/**
	 * ************************************* Setters ***************************************
	 */

	/**
	 * @param ArrayCollection $roles
	 */
	public function setRoles(ArrayCollection $roles)
	{
		foreach ($this->roles as $role) {
			$this->roles->removeElement($role);
		}

		foreach ($roles as $role) {
			$this->roles->add($role);
		}
	}

	/**
	 * @var mixed $isAdmin
	 */
	public function setIsAdmin($isAdmin)
	{
		$this->isAdmin = (int) filter_var($isAdmin, FILTER_VALIDATE_BOOLEAN);
		return $this;
	}

}
