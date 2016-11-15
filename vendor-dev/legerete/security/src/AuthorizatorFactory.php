<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security;

use Kdyby\Doctrine\EntityManager;
use Legerete\Security\Model\Entity\PrivilegeEntity;
use Legerete\Security\Model\Entity\RoleEntity;
use Nette\Security\IAuthorizator;

final class AuthorizatorFactory
{

	const ROLE_GUEST  = 'guest';
	const ROLE_ADMIN  = 'admin';

	/**
	 * @var Permission
	 */
	private $acl;

	/**
	 * @var array $privileges
	 */
	private $privilegesTypes = [];

	/**
	 * @var EntityManager $em
	 */
	private $em;

	/**
	 * Permission constructor.
	 *
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * @return IAuthorizator
	 */
	public function create()
	{
		$acl = $this->acl = new Permission;

		$this->addRoles($acl);

		$this->addResources($acl);
		$this->addPrivileges($acl);

		$this->addRolesFromDatabase();
		$this->addPrivilegesFromDatabase();
		return $acl;
	}


	public function addRoles(Permission $acl)
	{
		$acl->addRole(self::ROLE_GUEST);
		$acl->addRole(self::ROLE_ADMIN, self::ROLE_GUEST);
	}

	private function addRolesFromDatabase()
	{
		$roles = $this->roleRepository()->createQueryBuilder('r')
			->select('partial r.{id, name}, partial parents.{id, name}')
			->leftJoin('r.parents', 'parents')
			->getQuery()
			->getArrayResult();

		$sortedRoles = [];
		foreach ($roles as $role) {
			$parents = [];
			foreach ($role['parents'] as $parent) {
				$parents[$parent['name']] = $parent['name'];
			}
			$sortedRoles[$role['name']]['name'] = $role['name'];
			$sortedRoles[$role['name']]['parents'] = $parents;
		}

		uasort($sortedRoles, function ($a, $b) {
			if (array_key_exists($a['name'], $b['parents']) ) {
				return 0;
			}
			return 1;
		});

		foreach ($sortedRoles as $role) {
			$role['parents'][] = self::ROLE_GUEST;
			$this->acl->addRole($role['name'], $role['parents']);
		}
	}

	private function addPrivilegesFromDatabase()
	{
		$privileges = $this->privilegeRepository()->createQueryBuilder('p')
			->select('p, partial resource.{id, slug}, partial role.{id, name}')
			->leftJoin('p.resource', 'resource')
			->leftJoin('p.role', 'role')
			->getQuery()
			->getArrayResult();


		foreach ($privileges as $privilege) {
			if (! $this->acl->hasResource($privilege['resource']['slug'])) {
				$this->acl->addResource($privilege['resource']['slug']);
			}

			if ($privilege['allowed']) {
				$this->acl->allow($privilege['role']['name'], $privilege['resource']['slug'], $privilege['privilege']);
			} else {
				$this->acl->deny($privilege['role']['name'], $privilege['resource']['slug'], $privilege['privilege']);
			}
		}
	}

	public function addResources(Permission $acl)
	{
	}


	public function addPrivileges(Permission $acl)
	{
		$acl->allow(self::ROLE_ADMIN, IAuthorizator::ALL);
	}

	public function privilegeRepository()
	{
		return $this->em->getRepository(PrivilegeEntity::class);
	}

	public function roleRepository()
	{
		return $this->em->getRepository(RoleEntity::class);
	}
}
