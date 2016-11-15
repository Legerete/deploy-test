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
use Legerete\User\Model\SuperClass\UserSuperClass;
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Nette\Utils\Arrays;
use OTPHP\TOTP;

class UserModelService
{
	const PARTIAL_USER_SELECT = 'id, username, name, surname, email, phone, degree, status, avatar, isAdmin';

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
			$roles = Arrays::get($userData, 'roles', []);

			$user = new UserEntity(
				$userData['username'],
				$userData['name'],
				$userData['surname'],
				$userData['email'],
				$this->createRolesCollection($roles)
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
			->where('u.status != :statusDel')
			->setParameter('statusDel', 'del')
			->getQuery()
			->getArrayResult();
	}

	public function readUser($id)
	{
		$user = $this->userRepository()->createQueryBuilder('u')
			->select('partial u.{'.self::PARTIAL_USER_SELECT.', otp}, roles')
			->leftJoin('u.roles', 'roles')
			->where('u.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getSingleResult(AbstractQuery::HYDRATE_ARRAY);

		$otp = new TOTP($user['email'], $user['otp']);
		$user['otp'] = $otp->getProvisioningUri();

		return $user;
	}

	/**
	 * @param $data
	 * @param $setRoles
	 */
	public function updateUser($data, $setRoles = FALSE)
	{
		if (!isset($data['id'])) {
			throw new \InvalidArgumentException('User data not contains [id].');
		}

		$user = $this->findUserById($data['id']);
		$updateColumns = explode(', ', self::PARTIAL_USER_SELECT);
		if ($setRoles) {
			$updateColumns[] = 'roles';
		}

		foreach ($updateColumns as $column) {
			if (method_exists($user, $method = 'set'.ucfirst($column))) {
				$newValue = $data[$column] ?? FALSE;

				if ($method === 'setRoles') {
					$newValue = $newValue ? $this->createRolesCollection($newValue) : $this->createRolesCollection([]);
				};

				$user->$method($newValue);
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
		/**
		 * @var UserEntity $user
		 */
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
	 * @param string $username
	 * @return bool
	 */
	public function isUsernameAvailable($username)
	{
		$user = $this->userRepository()->createQueryBuilder('u')
			->select('partial u.{id}')
			->where('u.username = :username')
			->setParameter('username', $username)
			->getQuery()
			->getArrayResult();

		return !count($user) ?: FALSE;
	}

	/**
	 * @param string $email
	 * @return bool
	 */
	public function isEmailAvailable($email)
	{
		$user = $this->userRepository()->createQueryBuilder('u')
			->select('partial u.{id}')
			->where('u.email = :email')
			->setParameter('email', $email)
			->getQuery()
			->getArrayResult();

		return !count($user) ?: FALSE;
	}

	/**
	 * @param array $roles
	 * @return ArrayCollection
	 */
	private function createRolesCollection(array $roles = [])
	{
		$rolesCollection = new ArrayCollection();
		foreach ($roles as $role) {
			$rolesCollection->add($this->em->getReference(RoleEntity::class, (int) $role['id']));
		}

		return $rolesCollection;
	}

	public function blockUser($id)
	{
		$user = $this->userRepository()->find($id);
		$user->setStatus(UserSuperClass::USER_BLOCKED);
		$this->em->flush();

		return $this->readUser($id);
	}

	public function unblockUser($id)
	{
		$user = $this->userRepository()->find($id);
		$user->setStatus(UserSuperClass::USER_OK);
		$this->em->flush();

		return $this->readUser($id);
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