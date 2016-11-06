<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;

/**
 * @ORM\Entity
 * @Table(name="acl_privileges")
 * @ORM\HasLifecycleCallbacks
 */
class PrivilegeEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="RoleEntity", inversedBy="privileges")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
	 * @var RoleEntity $role
	 */
	protected $role;

	/**
	 * @ORM\OneToOne(targetEntity="ResourceEntity")
	 * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
	 * @var ResourceEntity $resource
	 */
	protected $resource;

	/**
	 * @ORM\Column(type="string")
	 * @var string $privilege
	 */
	protected $privilege;

	/**
	 * @ORM\Column(type="integer")
	 * @var integer $allowed
	 */
	protected $allowed;

	/**
	 * PrivilegeEntity constructor.
	 *
	 * @param RoleEntity $role
	 * @param ResourceEntity $resource
	 * @param string $privilege
	 * @param int $allowed
	 */
	public function __construct(RoleEntity $role, ResourceEntity $resource, $privilege, $allowed = false)
	{
		$this->role = $role;
		$this->resource = $resource;
		$this->privilege = $privilege;
		$this->allowed = $allowed;
	}

	/**
	 * ************************************* Getters ***************************************
	 */

	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return RoleEntity
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @return ResourceEntity
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * @return string
	 */
	public function getPrivilege()
	{
		return $this->privilege;
	}

	/**
	 * @return integer
	 */
	public function getAllowed()
	{
		return $this->allowed;
	}

	/**
	 * ************************************* Setters ***************************************
	 */

	/**
	 * @var RoleEntity $role
	 */
	public function setRole(RoleEntity $role)
	{
		$this->role = $role;
		return $this;
	}

	/**
	 * @var ResourceEntity $resource
	 */
	public function setResource(ResourceEntity $resource)
	{
		$this->resource = $resource;
		return $this;
	}

	/**
	 * @var string $privilege
	 */
	public function setPrivilege(string $privilege)
	{
		$this->privilege = $privilege;
		return $this;
	}

	/**
	 * @var integer $allowed
	 */
	public function setAllowed(int $allowed)
	{
		$this->allowed = $allowed;
		return $this;
	}

}
