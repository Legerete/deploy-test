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
	 * @ORM\OneToMany(targetEntity="PrivilegesEntity", mappedBy="role")
	 */
	protected $privileges;

	public function __construct()
	{
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

}
