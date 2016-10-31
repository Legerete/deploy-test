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
 * @Table(name="acl_resource")
 * @ORM\HasLifecycleCallbacks
 */
class ResourceEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string $name
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string $name
	 */
	protected $slug;

	/**
	 * ResourceEntity constructor.
	 *
	 * @param string $name
	 * @param string $slug
	 */
	public function __construct($name)
	{
		$this->name = $name;
		$this->setSlug($name);
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
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getSlug(): string
	{
		return $this->slug;
	}

	/**
	 * ************************************* Setters ***************************************
	 */

	/**
	 * @var string $name
	 */
	public function setName(string $name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @var string $name
	 */
	private function setSlug(string $name)
	{
		$this->slug = str_replace(':', '', $name);
		return $this;
	}

}
