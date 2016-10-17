<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\UserExtension
 */

namespace Legerete\UserSignInOtp\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Table;
use Nette\Security\Passwords;

/**
 * @ORM\Entity
 * @Table(name="user_otp")
 * @ORM\HasLifecycleCallbacks
 */
class UserOtpEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	public function __construct(){
	}

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\OneToOne(targetEntity="User")
	 */
	protected $user;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $status = 'ok';

	/**
	 * ************************************* Getters ***************************************
	 */

	public function getId()
	{
		return $this->id;
	}



	/**
	 * ************************************* Setters ***************************************
	 */



}

class InvalidUserOtpException extends \Exception{};