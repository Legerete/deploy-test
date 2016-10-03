<?php

namespace App\CoreModule\Entity;

use Nette\SmartObject,
	Nette\Utils\DateTime,
	Doctrine\ORM\Mapping as ORM;

/**
 * Class Client
 * @ORM\Entity
 * @ORM\Table(name="client")
 * @package App\CoreModule\Entity
 */
class Client
{
	use SmartObject;

	private

		/**
		 * Client ID
		 * @ORM\Id
		 * @ORM\Column(type="smallint", nullable=false, options={"unsigned":true})
		 * @ORM\GeneratedValue(strategy="AUTO")
		 * @var int
		 */
		$id,

		/**
		 * Client's login name
		 * @ORM\Column(type="string", length=64, unique=true, nullable=false, options={"comment":"Client's login name"})
		 * @var string
		 */
		$login,

		/**
		 * Client's salted password hash
		 * @ORM\Column(type="string", length=60, nullable=false, options={"fixed":true, "comment":"Client's salted password hash"})
		 * @var string
		 */
		$password,

		/**
		 * Datetime of last client login
		 * @ORM\Column(name="last_login", type="datetime", nullable=true, options={"comment":"Datetime of last login user"})
		 * @var DateTime
		 */
		$lastLogin,

		/**
		 * Is client active?
		 * @ORM\Column(type="boolean", nullable=false, options={"default":true, "comment":"Is active client?"})
		 * @var bool
		 */
		$isActive,

		/**
		 * Client's creation datetime
		 * @ORM\Column(type="datetime", nullable=false, options={"comment":"Client's creation datetime"})
		 * @var NULL|DateTime
		 */
		$created;

	//<editor-fold defaultstate="collapsed" desc="Getters">
	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @return NULL|DateTime
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	/**
	 * @return bool
	 */
	public function getIsActive()
	{
		return $this->isActive;
	}

	/**
	 * @return DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}
	//</editor-fold>

	//<editor-fold defaultstate="collapsed" desc="Setters">
	/**
	 * @param int $id
	 * @return Client
	 */
	public function setId($id)
	{
		$this->id = (int)$id;

		return $this;
	}

	/**
	 * @param string $login
	 * @return Client
	 */
	public function setLogin($login)
	{
		$this->login = (string)$login;

		return $this;
	}

	/**
	 * @param string $password
	 * @return Client
	 */
	public function setPassword($password)
	{
		$this->password = (string)$password;

		return $this;
	}

	/**
	 * @param DateTime $lastLogin
	 * @return Client
	 */
	public function setLastLogin(DateTime $lastLogin)
	{
		$this->lastLogin = $lastLogin;

		return $this;
	}

	/**
	 * @param bool $isActive
	 * @return Client
	 */
	public function setIsActive($isActive)
	{
		$this->isActive = (TRUE === $isActive);

		return $this;
	}

	/**
	 * @param DateTime $created
	 * @return Client
	 */
	public function setCreated(DateTime $created)
	{
		$this->created = $created;

		return $this;
	}
	//</editor-fold>
}
