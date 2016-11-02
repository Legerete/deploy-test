<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoAcl\Model\Service;

use Doctrine\ORM\AbstractQuery;
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

	private function prepareReadRoleQuery()
	{
		return $this->roleRepository()->createQueryBuilder('r')
			->select('r, p, res')
			->leftJoin('r.privileges', 'p')
			->leftJoin('p.resource', 'res');
	}

	public function readRole($roleId)
	{
		return $this->prepareReadRoleQuery()
			->where('r.id = :roleId')
			->setParameter('roleId', $roleId)
			->setMaxResults(1)
			->getQuery()
			->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
	}

	public function readRoles($ignoreId = null)
	{
		$query = $this->prepareReadRoleQuery();

		if ($ignoreId) {
			$query = $query->andWhere('r.id <> :ignoreId')
				->setParameter('ignoreId', $ignoreId);
		}

		return $query->getQuery()
			->getArrayResult();
	}

	public function readRolesWithResources($ignore = null)
	{
		$roles = $this->readRoles($ignore);
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

	public function createRoles($roles)
	{
		$this->em->beginTransaction();
		foreach ($roles as $role) {
			$this->createRole($role);
		}
		$this->em->commit();

		return $roles;
	}

	public function createRole($role)
	{
		$role = new RoleEntity($role['title'], $role['name']);
	}

	public function setRoleParents($role, $parents)
	{

	}

	public function updateRoles($roles)
	{
		$this->em->beginTransaction();
		foreach ($roles as $role) {
			$this->updateRole($role);
		}
		$this->em->commit();

		return $roles;
	}

	public function updateRole($role)
	{
		$role = $this->roleRepository()->find($role['id']);
	}

	/**
	 * @return array
	 */
	public function getResources()
	{
		return $this->permissions->getResourcesPrivileges();
	}

	/**
	 * @return array
	 */
	public function getAllPrivilegesOfResources()
	{
		return $this->permissions->getAllPrivilegesOfResources();
	}

	/**
	 * @return array
	 */
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