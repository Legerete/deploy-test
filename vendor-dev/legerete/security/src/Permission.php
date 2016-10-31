<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security;


use Nette\Utils\Arrays;

class Permission extends \Nette\Security\Permission
{
	const PRIVILEGE_SHOW = 'show';
	const PRIVILEGE_READ_ALL = 'readAll';
	const PRIVILEGE_READ_MY = 'readMy';
	const PRIVILEGE_CREATE = 'create';
	const PRIVILEGE_UPDATE = 'update';
	const PRIVILEGE_DESTROY = 'destroy';

	/**
	 * @var array $resourcePrivileges
	 */
	private $resourcePrivileges = [];

	/**
	 * @param string $resource
	 * @param string $privilege
	 */
	public function addResourcePrivilege(string $resource, string $privilege)
	{
		if (!$this->hasResource($resource)) {
			$this->addResource($resource);
		}

		$this->resourcePrivileges[$resource][$privilege] = $privilege;
	}

	/**
	 * @param string $resource
	 * @param array $privileges
	 */
	public function addResourcePrivileges(string $resource, array $privileges)
	{
		foreach ($privileges as $privilege) {
			$this->addResourcePrivilege($resource, $privilege);
		}
	}

	/**
	 * @param string $resource
	 * @return array
	 */
	public function getResourcePrivileges(string $resource)
	{
		if ($this->hasResource($resource)) {
			return array_values(Arrays::get($this->resourcePrivileges, $resource, []));
		}

		$this->addResource($resource);
		return [];
	}

	public function getResourcesPrivileges()
	{
		return $this->resourcePrivileges;
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return bool
	 */
	public function hasResourcePrivilege(string $resource, string $privilege)
	{
		$res = Arrays::get($this->resourcePrivileges, $resource, []);
		return (bool) array_search($privilege, $res);
	}

	/**
	 * @return array
	 */
	public function getAllPrivilegesOfResources()
	{
		$result = [];

		foreach ($this->resourcePrivileges as $privileges) {
			$result = array_merge($result, $privileges);
		}

		$result = array_keys($result);
		sort($result);
		return $result;
	}
}