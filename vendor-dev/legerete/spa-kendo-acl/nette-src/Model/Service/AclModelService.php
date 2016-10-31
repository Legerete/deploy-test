<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoAcl\Model\Service;

use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Legerete\Security\Model\Entity\RoleEntity;
use Legerete\Security\Model\Entity\UserEntity;
use Legerete\Security\Permission;
use Nette\Security\IAuthorizator;

class AclModelService
{
	/**
	 * @var Permission $permissions
	 */
	private $permissions;

	/**
	 * @var array $resources
	 */
	private $resources;

	/**
	 * @var EntityManager
	 */
	private $em;

	public function __construct(IAuthorizator $permissions, EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->permissions = $permissions;
		$this->resources = $this->permissions->getResources();
	}

	public function readRoles()
	{
		return $this->roleRepository()->createQueryBuilder('r')
			->select('r, p, res')
			->leftJoin('r.privileges', 'p')
			->leftJoin('p.resource', 'res')
			->getQuery()
			->getArrayResult();
	}

	public function readRolesWithResources()
	{
		$roles = $this->readRoles();
		$result = [];

		foreach ($roles as $roleKey => $roleData) {
			$result[$roleKey] = [
				'id' => $roleData['id'],
				'name' => $roleData['name'],
				'title' => $roleData['title'],
				'resources' => [],
			];

			foreach ($roleData['privileges'] as $privilege) {
				$resourceKey = $privilege['resource']['slug'];
				$result[$roleKey]['resources'][$resourceKey] = $result[$roleKey]['resources'][$resourceKey] ?? [];

				$result[$roleKey]['resources'][$resourceKey][$privilege['privilege']] = (bool) $privilege['allowed'];
			}

		}

		return $result;
	}

	public function getResources()
	{
		return $this->permissions->getResourcesPrivileges();
	}

	public function getAllPrivilegesOfResources()
	{
		return $this->permissions->getAllPrivilegesOfResources();
	}

	public function getResourcePrivileges()
	{
		return $this->permissions->getResourcesPrivileges();
	}

	/**
	 * @return EntityRepository
	 */
	private function userRepository() : EntityRepository
	{
		return $this->em->getRepository(UserEntity::class);
	}

	/**
	 * @return EntityRepository
	 */
	private function roleRepository() : EntityRepository
	{
		return $this->em->getRepository(RoleEntity::class);
	}

}