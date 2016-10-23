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
	const AVATAR_NAMESPACE = 'user-avatar';
	const AVATAR_DIMENSIONS_LARGE = '350x350';
	const AVATAR_DIMENSIONS_SMALL = '80x80';

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string $username
	 */
	protected $username;

	/**
	 * @ORM\Column(type="string")
	 * @var string $password
	 */
	protected $password;

	/**
	 * @ORM\Column(type="string")
	 * @var string $passwordResetHash
	 */
	protected $passwordResetHash;

	/**
	 * @ORM\Column(type="integer")
	 * @var integer $passwordNeedReset
	 */
	protected $passwordNeedReset = 0;

	/**
	 * @ORM\Column(type="date")
	 * @var \DateTime $passwordLastChange
	 */
	protected $passwordLastChange;

	/**
	 * @ORM\Column(type="string")
	 * @var string $name
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string")
	 * @var string $surname
	 */
	protected $surname;

	/**
	 * @ORM\Column(type="string")
	 * @var string $degree
	 */
	protected $degree;

	/**
	 * @ORM\Column(type="string")
	 * @var string $email
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string")
	 * @var string $phone
	 */
	protected $phone;

	/**
	 * @ORM\Column(type="string")
	 * @var string $avatar
	 */
	protected $avatar;

	/**
	 * @ORM\Column(type="string")
	 * @var string $verificationHash
	 */
	protected $verificationHash;

	/**
	 * @ORM\Column(type="string")
	 * @var string $roles
	 */
	protected $roles;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime $registerNotificationSent
	 */
	protected $registerNotificationSent = null;

	/**
	 * @ORM\Column(type="string")
	 * @var string $status
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
		$this->setUsername($username)
			->setName($name)
			->setSurname($surname)
			->setEmail($email)
			->setRoles($roles);
	}

	/**
	 * ************************************* Getters ***************************************
	 */

	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return integer
	 */
	public function getIdentityNo()
	{
		return $this->identityNo;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @return string
	 */
	public function getPasswordResetHash()
	{
		return $this->passwordResetHash;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @return string
	 */
	public function getAvatar()
	{
		return $this->avatar;
	}

	/**
	 * @return mixed
	 */
	public function getVerificationHash()
	{
		return $this->verificationHash;
	}

	/**
	 * @return array
	 */
	public function getRoles()
	{
		return Json::decode($this->roles);
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}


	/**
	 * ************************************* Setters ***************************************
	 */


	public function setUsername(string $username)
	{
		$this->username = trim($username);
		return $this;
	}

	/**
	 * @param string $password
	 * @return $this
	 */
	public function setPassword($password)
	{
		$this->password = self::calculateHash($password);
		return $this;
	}

	/**
	 * @param string $passwordResetHash
	 * @return $this
	 */
	public function setPasswordResetHash($passwordResetHash)
	{
		$this->passwordResetHash = $passwordResetHash;
		return $this;
	}

	/**
	 * @param integer $passwordNeedReset
	 * @return $this
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

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName(string $name)
	{
		$this->name = trim($name);
		return $this;
	}

	/**
	 * @param string $surname
	 * @return $this
	 */
	public function setSurname(string $surname)
	{
		$this->surname = trim($surname);
		return $this;
	}

	/**
	 * @param string $email
	 * @return $this
	 */
	public function setEmail($email)
	{
		$this->email = trim($email);
		return $this;
	}

	/**
	 * @var mixed $phone
	 * @return $this
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
		return $this;
	}

	/**
	 * @var string $avatar
	 * @return $this
	 */
	public function setAvatar($avatar)
	{
		$this->avatar = $avatar;
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