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
use Legerete\Security\Model\Entity\PrivilegeEntity;
use Legerete\Security\Model\Entity\ResourceEntity;
use Legerete\Security\Model\Entity\RoleEntity;
use Legerete\Security\Permission;
use Nette\Security\IAuthorizator;
use Nette\Utils\Random;
use Nette\Utils\Strings;

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

	/**
	 * @param integer $roleId
	 * @return array
	 */
	public function readRoleWithResources($roleId)
	{
		$role = $this->readRole($roleId);
		$role['resources'] = $this->reformatPrivilegesToResources($role['privileges']);
		unset($role['privileges']);

		return $role;
	}

	/**
	 * @param null|integer $ignore
	 * @return array
	 */
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

			$result[$roleKey]['resources'] = $this->reformatPrivilegesToResources($roleData['privileges']);
		}

		return $result;
	}

	/**
	 * @param array $privileges
	 * @return array
	 */
	private function reformatPrivilegesToResources($privileges)
	{
		$resources = [];
		foreach ($privileges as $privilege) {
			$resourceKey = $privilege['resource']['slug'];
			$resources[$resourceKey] = $resources[$resourceKey] ?? [];
			$resources[$resourceKey][$privilege['privilege']] = (bool) $privilege['allowed'];
		}

		return $resources;
	}

	/**
	 * @param $roles
	 * @param bool $returnWithResources
	 * @return array
	 */
	public function createRoles($roles, $returnWithResources = false)
	{
		$newRoles = [];
		$this->em->beginTransaction();
		foreach ($roles as $role) {
			$newRoles[] = $this->createRole($role, $returnWithResources);
		}
		$this->em->commit();

		return $newRoles;
	}

	/**
	 * @param array $role
	 * @param bool $returnWithResources
	 * @return array
	 */
	public function createRole(array $role, $returnWithResources = false) : array
	{
		$roleName = $this->generateUniqueRoleName($role['title']);

		$this->em->beginTransaction();

		$roleEntity = new RoleEntity($role['title'], $roleName);
		$this->em->persist($roleEntity);
		$rolePrivileges = $this->createPrivileges($roleEntity, $role['resources']);
		$roleEntity->setPrivileges($rolePrivileges);

		$this->em->commit();

		if ($returnWithResources) {
			return $this->readRoleWithResources($roleEntity->id);
		}
		return $this->readRole($roleEntity->id);
	}

	/**
	 * @param RoleEntity $role
	 * @param array $privileges
	 *
	 * @return array
	 */
	private function createPrivileges(RoleEntity $role, array $privileges = []) : array
	{
		$privilegesEntities = [];

		foreach ($privileges as $resourceName => $privilegeAction) {
			$resource = $this->resourceRepository()->findOneBy(['slug' => $resourceName]);

			foreach ($privilegeAction as $privilegeName => $allowed) {
				$privilege = $this->privilegeRepository()->findOneBy([
					'role' => $role->getId(),
					'resource' => $resource->getId(),
					'privilege' => $privilegeName
				]);

				if (!$privilege) {
					$privilege = new PrivilegeEntity($role, $resource, $privilegeName);
				}
				$privilege->setAllowed(filter_var($allowed, FILTER_VALIDATE_BOOLEAN));

				$privilegesEntities[] = $privilege;
				$this->em->persist($privilege)->flush();
			}
		}

		return $privilegesEntities;
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
	 * @param $roleTitle
	 * @return string
	 */
	private function generateUniqueRoleName($roleTitle)
	{
		$webalizedTitle = Strings::webalize($roleTitle);

		if ($this->checkIfRoleNameExists($webalizedTitle))
		{
			do {
				$randSuffix = Random::generate(6);
				$uniqueName = $webalizedTitle . '-' . $randSuffix;
				$nameExists = $this->checkIfRoleNameExists($uniqueName);
			} while ($nameExists === true);

			return $uniqueName;
		}

		return $webalizedTitle;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	private function checkIfRoleNameExists(string $name) : bool
	{
		$roleExists = $this->roleRepository()->createQueryBuilder('r')
			->select('partial r.{id}')
			->where('r.name = :name')
			->setParameter('name', $name)
			->setMaxResults(1)
			->getQuery()
			->getScalarResult();

		return (bool) $roleExists;
	}

	/**
	 * @return EntityRepository
	 */
	private function privilegeRepository()
	{
		return $this->em->getRepository(PrivilegeEntity::class);
	}

	/**
	 * @return EntityRepository
	 */
	private function resourceRepository()
	{
		return $this->em->getRepository(ResourceEntity::class);
	}

	/**
	 * @return EntityRepository
	 */
	private function roleRepository()
	{
		return $this->em->getRepository(RoleEntity::class);
	}

}