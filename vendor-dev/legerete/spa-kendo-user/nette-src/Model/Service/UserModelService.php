<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoUser\Model\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Legerete\Security\Model\Entity\RoleEntity;
use Legerete\Security\Model\Entity\UserEntity;
use Legerete\Spa\KendoUser\Model\Exception\UserNotFoundException;
use Nette\Application\BadRequestException;
use Nette\Security\User;

class UserModelService
{
	const PARTIAL_USER_SELECT = 'id, username, name, surname, email, phone, degree, status, avatar';

	/**
	 * @var User $user
	 */
	private $user;

	/**
	 * @var EntityManager $em
	 */
	private $em;

	/**
	 * SchedulerModelService constructor.
	 *
	 * @param EntityManager $em
	 * @param User $user
	 */
	public function __construct(EntityManager $em, User $user, string $timeZone = null)
	{
		$this->em = $em;
		$this->user = $user;
	}

	/**
	 * @param array $eventData
	 */
	public function createNewUser($userData = [])
	{
		if (count($userData)) {
			$user = new UserEntity(
				$userData['username'],
				$userData['name'],
				$userData['surname'],
				$userData['email'],
				$this->createRolesCollection($userData['roles'])
			);

			$user->setPhone($userData['phone']);

			$this->em->persist($user)->flush();
			return $user;
		} else {
			throw new BadRequestException('Empty $userData argument.');
		}
	}

	/**
	 * @return array
	 */
	public function readUsers() : array
	{
		return $this->userRepository()->createQueryBuilder('u')
			->select('partial u.{'.self::PARTIAL_USER_SELECT.'}, roles')
			->leftJoin('u.roles', 'roles')
			->getQuery()
			->getArrayResult();
	}

	public function readUser($id)
	{
		return $this->userRepository()->createQueryBuilder('u')
			->select('partial u.{'.self::PARTIAL_USER_SELECT.'}, roles')
			->leftJoin('u.roles', 'roles')
			->where('u.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
	}

	/**
	 * @param $data
	 */
	public function updateUser($data)
	{
		if (!isset($data['id'])) {
			throw new \InvalidArgumentException('User data not contains [id].');
		}

		$user = $this->findUserById($data['id']);

		foreach ($data as $key => $value) {
			if (method_exists($user, $method = 'set'.ucfirst($key))) {

				if ($method === 'setRoles') {
					$user->setRoles($this->createRolesCollection($data['roles']));
				} else {
					$user->$method($value);
				}
			}
		}

		if (isset($data['newAvatar'])) {
			$user->setAvatar($data['newAvatar']);
		}

		$this->em->flush();
		return $this->readUser($data['id']);
	}

	/**
	 * @param $id
	 *
	 * @return UserEntity
	 * @throws UserNotFoundException
	 */
	private function findUserById($id) : UserEntity
	{
		$user = $this->userRepository()->find($id);

		if (!$user) {
			throw new UserNotFoundException("User with id [{$id}] not found.");
		}

		return $user;
	}

	/**
	 * @return array
	 */
	public function getAvailableRoles()
	{
		return $this->roleRepository()->createQueryBuilder('r')
			->select('r')
			->orderBy('r.title', 'ASC')
			->getQuery()
			->getArrayResult();
	}

	/**
	 * @param array $roles
	 * @return ArrayCollection
	 */
	private function createRolesCollection(array $roles = [])
	{
		$rolesCollection = new ArrayCollection();

		foreach ($roles as $roleId) {
			$rolesCollection->add($this->em->getReference(RoleEntity::class, (int) $roleId));
		}

		return $rolesCollection;
	}

	/**
	 * @return EntityRepository
	 */
	private function roleRepository() : EntityRepository
	{
		return $this->em->getRepository(RoleEntity::class);
	}

	/**
	 * @return EntityRepository
	 */
	private function userRepository() : EntityRepository
	{
		return $this->em->getRepository(UserEntity::class);
	}

}