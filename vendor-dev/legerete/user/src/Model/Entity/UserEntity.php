<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\User
 */

namespace Legerete\User\Model\Entity;

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
	 * UserEntity constructor.
	 *
	 * @param string $username
	 * @param string $name
	 * @param string $surname
	 * @param string $email
	 */
	public function __construct($username, $name, $surname, $email)
	{
		parent::__construct($username, $name, $surname, $email);
	}
}
