<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoAcl\Model\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Legerete\Security\Model\Entity\PrivilegeEntity;
use Legerete\Security\Model\Entity\ResourceEntity;
use Legerete\Security\Model\Entity\RoleEntity;
use Legerete\Security\Permission;
use Nette\Security\IAuthorizator;
use Nette\Utils\Arrays;
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

	/**
	 * AclModelService constructor.
	 * @param IAuthorizator $permissions
	 * @param EntityManager $entityManager
	 */
	public function __construct(IAuthorizator $permissions, EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->permissions = $permissions;
		$this->resources = $this->permissions->getResources();
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function prepareReadRoleQuery()
	{
		return $this->roleRepository()->createQueryBuilder('r')
			->select('r, privileges, resources, parents')
			->leftJoin('r.privileges', 'privileges')
			->leftJoin('privileges.resource', 'resources')
			->leftJoin('r.parents', 'parents')
			->orderBy('r.title');
	}

	/**
	 * @param $roleId
	 * @return null|array
	 * [
	 * 	'id' => 1,
	 * 	'name' => 'guest',
	 * 	'title' => 'Guest',
	 * 	'privileges' => [
	 * 		0 => [
	 * 			'id' => 1,
	 * 			'privilege' => 'create',
	 * 			'allowed' => 1,
	 * 			'resource' => [
	 * 				'id' => 1,
	 * 				'name' => 'LeSpaAcl:Acl:Acl',
	 * 				'slug' => 'LeSpaAclAclAcl',
	 * 			],
	 * 		],
	 * 		1 => [
	 * 			'id' => 733,
	 * 			'privilege' => 'show',
	 * 			'allowed' => 0,
	 * 			'resource' => [
	 * 				'id' => 1,
	 * 				'name' => 'LeSpaAcl:Acl:Acl',
	 * 				'slug' => 'LeSpaAclAclAcl',
	 * 			],
	 * 		],
	 * 	],
	 * ]
	 */
	public function readRole($roleId)
	{
		return $this->prepareReadRoleQuery()
			->where('r.id = :roleId')
			->setParameter('roleId', $roleId)
			->getQuery()
			->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
	}

	/**
	 * @param null $ignoredRoleId
	 * @return array {@inheritdoc}
	 */
	public function readRoles($ignoredRoleId = null)
	{
		$query = $this->prepareReadRoleQuery();

		if ($ignoredRoleId) {
			$query = $query->andWhere('r.id <> :ignoreId')
				->setParameter('ignoreId', $ignoredRoleId);
		}

		return $query->getQuery()
			->getArrayResult();
	}

	/**
	 * @param integer $roleId
	 * @return array
	 * [
	 * 	'id' => 1,
	 * 	'name' => 'guest',
	 * 	'title' => 'Guest',
	 * 	'resources' => [
	 * 		'LeSpaAclAclAcl' => [
	 * 			'create' => TRUE,
	 * 			'show' => FALSE,
	 * 			'readAll' => FALSE,
	 * 			'update' => FALSE,
	 * 			'destroy' => FALSE,
	 * 		],
	 * 		'LeSignInUserSignInSign' => [
	 * 			'show' => FALSE,
	 * 		],
	 * 	]
	 */
	public function readRoleWithResources($roleId)
	{
		$role = $this->readRole($roleId);
		$role['resources'] = $this->reformatPrivilegesToResources($role['privileges']);
		unset($role['privileges']);

		return $role;
	}

	/**
	 * @inheritdoc self::readRoleWithResources()
	 * @param null|integer $ignoredRoleId
	 * @return array
	 */
	public function readRolesWithResources($ignoredRoleId = null)
	{
		$roles = $this->readRoles($ignoredRoleId);
		$result = [];

		foreach ($roles as $roleKey => $roleData) {
			$result[$roleKey] = [
				'id' => $roleData['id'],
				'name' => $roleData['name'],
				'title' => $roleData['title'],
				'parents' => $roleData['parents'],
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

		foreach ($newRoles as $key => $role) {
			$parents = Arrays::get($roles[$key], 'parents', []);
			$this->setRoleParents($role, $parents);
		}

		$this->em->flush();
		$this->em->commit();

		$result = [];
		foreach ($newRoles as $role) {
			if ($returnWithResources) {
				$result[] = $this->readRoleWithResources($role->id);
			} else {
				$result[] = $this->readRole($role->id);
			}
		}



		return $result;
	}

	/**
	 * @param array $role
	 * @param bool $returnWithResources
	 * @return RoleEntity
	 */
	public function createRole(array $role, $returnWithResources = false) : RoleEntity
	{
		$roleName = $this->generateUniqueRoleName($role['title']);

		$this->em->beginTransaction();

		$roleEntity = new RoleEntity($role['title'], $roleName);
		$this->em->persist($roleEntity);
		$rolePrivileges = $this->createPrivileges($roleEntity, $role['resources']);
		$roleEntity->setPrivileges($rolePrivileges);

		$this->em->flush();
		$this->em->commit();

		return $roleEntity;
	}

	/**
	 * @param RoleEntity $role
	 * @param array $privileges
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

	/**
	 * @param array $roles
	 * @param bool $returnWithResources
	 * @return array
	 */
	public function updateRoles($roles, $returnWithResources = false)
	{
		$updatedRoles = [];
		$this->em->beginTransaction();
		foreach ($roles as $role) {
			$updatedRoles[] = $this->updateRole($role, $returnWithResources);
		}
		$this->em->flush();
		$this->em->commit();

		return $updatedRoles;
	}

	/**
	 * @param array $roleData
	 * @param bool $returnWithResources
	 * @return array|mixed
	 */
	public function updateRole($roleData, $returnWithResources = false)
	{
		/**
		 * @var RoleEntity $role
		 */
		$role = $this->roleRepository()->find($roleData['id']);
		$parents = Arrays::get($roleData, 'parents', []);

		$role->setTitle($roleData['title']);
		$this->createPrivileges($role, $roleData['resources']);
		$this->setRoleParents($role, $parents);
		$this->em->flush();

		if ($returnWithResources) {
			return $this->readRoleWithResources($roleData['id']);
		} else {
			return $this->readRole($roleData['id']);
		}
	}

	private function setRoleParents(RoleEntity $role, $parents = [])
	{
		$parentsCollection = new ArrayCollection();

		foreach ($parents as $parent) {
			if (isset($parent['id']) && ! empty($parent['id'])) {
				$parentEntity = $this->em->getReference(RoleEntity::class, $parent['id']);
			} else {
				$parentEntity = $this->roleRepository()->findOneBy(['title' => $parent['title']]);

				if (!$parentEntity) {
					$parentEntity = new RoleEntity($parent['title'], $this->generateUniqueRoleName($parent['title']));
				}
				$this->em->persist($parentEntity);
			}

			$parentsCollection->add($parentEntity);
		}

		$role->setParents($parentsCollection);
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