<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\UserExtension
 */

namespace Legerete\User\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Nette\Security\Passwords;
use Nette\Utils\Json;

/**
 * @ORM\Entity
 * @Table(name="user")
 * @ORM\HasLifecycleCallbacks
 */
class UserEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	/**
	 * Events
	 * @var array
	 */
	public $onCreate = [];
	public $onUpdate = [];

	/**
	 * Roles
	 */
	const DEFAULT_ROLE = self::ROLE_REGISTERED;
	const ROLE_SUPERADMIN = 'SuperAdmin';
	const ROLE_ADMIN = 'Admin';
	const ROLE_COUPE_OWNER = 'CoupeOwner';
	const ROLE_COUPE_PATRON = 'CoupePatron';
	const ROLE_VENDOR = 'Vendor';
	const ROLE_REGISTERED = 'Registered';



	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $username;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $password;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $passwordResetHash;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $passwordNeedReset = 0;

	/**
	 * @ORM\Column(type="date")
	 */
	protected $passwordLastChange;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $surname;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $verificationHash;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $roles;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $registerNotificationSent = null;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $status = 'ok';

	/**
	 * UserEntity constructor.
	 *
	 * @param string $username
	 * @param string $name
	 * @param string $surname
	 * @param string $email
	 * @param array $roles
	 */
	public function __construct($username, $name, $surname, $email, $roles)
	{
		$this->username = $username;
		$this->name = $name;
		$this->surname = $surname;
		$this->email = $email;
		$this->roles = $this->setRoles($roles);
	}

	/**
	 * ************************************* Getters ***************************************
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getIdentityNo()
	{
		return $this->identityNo;
	}

	public function getUsername()
	{
		return $this->username;
	}

	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @return mixed
	 */
	public function getPasswordResetHash()
	{
		return $this->passwordResetHash;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getSurname()
	{
		return $this->surname;
	}

	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return mixed
	 */
	public function getVerificationHash()
	{
		return $this->verificationHash;
	}

	public function getRoles()
	{
		return $this->roles;
	}

	public function getStatus()
	{
		return $this->status;
	}


	/**
	 * ************************************* Setters ***************************************
	 */


	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}

	public function setPassword($password)
	{
		$this->password = self::calculateHash($password);
		return $this;
	}

	/**
	 * @param mixed $passwordResetHash
	 */
	public function setPasswordResetHash($passwordResetHash)
	{
		$this->passwordResetHash = $passwordResetHash;

		return $this;
	}

	/**
	 * @param mixed $passwordNeedReset
	 */
	public function setPasswordNeedReset($passwordNeedReset)
	{
		$this->passwordNeedReset = $passwordNeedReset;

		return $this;
	}

	/**
	 * @param mixed $passwordLastChange
	 */
	public function setPasswordLastChange($passwordLastChange)
	{
		$this->passwordLastChange = $passwordLastChange;

		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @param mixed $verificationHash
	 */
	public function setVerificationHash($verificationHash)
	{
		$this->verificationHash = $verificationHash;

		return $this;
	}

	public function setRoles($roles)
	{
		$this->roles = Json::encode($roles);
		return $this;
	}

	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	public function destroy()
	{
		$this->status = 'del';
		return $this;
	}

	public function isOk()
	{
		return $this->status === 'ok';
	}

	public function isDel()
	{
		return $this->status === 'del';
	}

	public function registerNotificationIsSent()
	{
		$this->registerNotifSended = new \DateTime();
	}

	/**
	 * Generate and set new Password to entity
	 * @author Petr Horacek <petr.horacek@wunderman.cz>
	 * @return string
	 */
	public function generateNewPassword()
	{
		$specialChars = substr(str_shuffle('!"#$%&()*+,.-/:<=>?_'), 0, rand(1,2));
		$password = str_shuffle(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8 - strlen($specialChars)).$specialChars);
		$this->setPassword($password);
		return $password;
	}

	/**
	 * Computes salted password hash.
	 * @author Petr Horacek <petr.horacek@wunderman.cz>
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		return Passwords::hash($password);

		/*if ($password === \Nette\Utils\Strings::upper($password)) { // perhaps caps lock is on
			$password = \Nette\Utils\Strings::lower($password);
		}
		return crypt($password, $salt ?: '$2a$07$jhiuzui' . \Nette\Utils\Random::generate(22));*/
	}

}

class InvalidUserException extends \Exception{};