<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security;

use Nette\Security\Permission;
use Nette\Security\IAuthorizator;

final class AuthorizatorFactory
{

	const ROLE_GUEST  = 'guest';
	const ROLE_CLIENT = 'client';
	const ROLE_ADMIN  = 'admin';

	/**
	 * @var Permission
	 */
	private $acl;

	/**
	 * @return IAuthorizator
	 */
	public function create()
	{
		$acl = $this->acl = new Permission;

		$this->addRoles($acl);
		$this->addResources($acl);
		$this->addPrivileges($acl);
		return $acl;
	}


	public function addRoles(Permission $acl)
	{
		$acl->addRole(self::ROLE_GUEST);
		$acl->addRole(self::ROLE_CLIENT, self::ROLE_GUEST);
		$acl->addRole(self::ROLE_ADMIN, self::ROLE_CLIENT);
	}


	public function addResources(Permission $acl)
	{
		/**
		 * PublicModule
		 */
		$acl->addResource('User:Sign');
		$acl->addResource('User:LostPassword');
		$acl->addResource('Legerete:User:ForgotPassword');
//		$acl->addResource('LeSignIn:UserSignIn:Sign');

	}


	public function addPrivileges(Permission $acl)
	{
		/**
		 * Role guest
		 */
//		$acl->allow(self::ROLE_GUEST, 'Public');
		$acl->allow(self::ROLE_GUEST, 'User:Sign');
		$acl->allow(self::ROLE_GUEST, 'User:LostPassword');
		$acl->allow(self::ROLE_GUEST, 'Legerete:User:ForgotPassword');
//		$acl->allow(self::ROLE_GUEST, 'LeSpaScheduler:Scheduler:Scheduler');
//		$acl->allow(self::ROLE_GUEST, 'LeSignIn:UserSignIn:Sign');
//		$acl->deny(self::ROLE_GUEST, 'Public:Users:Account');
	}
}
