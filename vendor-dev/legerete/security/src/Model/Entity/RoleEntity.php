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

/**
 * @ORM\Entity
 * @Table(name="acl_role")
 * @ORM\HasLifecycleCallbacks
 */
class RoleEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string $status
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string $status
	 */
	protected $title;

	/**
	 * @ORM\OneToMany(targetEntity="PrivilegeEntity", mappedBy="role")
	 * @var Collection $privileges
	 */
	protected $privileges;

	/**
	 * @ORM\ManyToMany(targetEntity="RoleEntity")
	 * @ORM\JoinTable(name="acl_role_parent",
	 * 		joinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")},
	 * 		inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
	 * )
	 * @var Collection $parents
	 */
	protected $parents;

	/**
	 * RoleEntity constructor.
	 *
	 * @param string $title
	 */
	public function __construct($title, $name, $parents = [])
	{
		$this->title = $title;
		$this->name = $name;
	}

	/**
	 * ************************************* Getters ***************************************
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return Collection
	 */
	public function getPrivileges()
	{
		return $this->privileges;
	}

	/**
	 * ************************************* Setters ***************************************
	 */

	/**
	 * @var string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @var string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	public function setParents($parents = [])
	{
		foreach ($this->parents as $parent) {
			if ($parent) // @todo hledani v doslych rodicich
			{
				$this->parents->removeElement($parent);
			}
		}

		foreach ($parents as $parent) {
			if (! $this->parents->contains($parent)) {
				$this->parents->add($parent);
			}
		}

	}

	/**
	 * @param array $privileges
	 * @return $this
	 */
	public function setPrivileges(array $privileges)
	{
		foreach ($privileges as $privilege) {
			$this->setPrivilege($privilege);
		}

		return $this;
	}

	/**
	 * @param PrivilegeEntity $privilege
	 * @return $this
	 */
	public function setPrivilege(PrivilegeEntity $privilege)
	{
		if (!$this->privileges) {
			$this->privileges = new ArrayCollection();
		}
//		if ($privilege->getAllowed() === false && $this->privileges->contains($privilege))
//		{
//			$this->privileges->remove($privilege);
//		} else if ($privilege->getAllowed() === true) {
			$this->privileges->add($privilege);
//		}

		return $this;
	}

}
