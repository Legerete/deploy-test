<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\UserSignIn
 */

namespace Legerete\UserSignInModule\Model\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NoResultException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Legerete\Security\Model\Entity\RoleEntity;
use Legerete\Security\Model\Entity\UserEntity;
use Legerete\Spa\KendoUser\Model\Exception\UserNotFoundException;
use Legerete\User\Model\SuperClass\UserSuperClass;
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Nette\Utils\Arrays;

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
	 * @param $userId
	 * @param $hash
	 * @return null|UserSuperClass
	 */
	public function getUserEntityByIdAndActivationToken($userId, $hash)
	{
		return $this->userRepository()->findOneBy(['id' => $userId, 'verificationHash' => $hash, 'status' => UserSuperClass::USER_NEW]);
	}

	/**
	 * @param integer $userId
	 * @param string $password
	 * @return bool
	 * @throws UserNotFoundException
	 */
	public function setUserPassword($userId, $password)
	{
		/**
		 * @var UserSuperClass $user
		 */
		$user = $this->userRepository()->find($userId);

		if (!$user) {
			throw new UserNotFoundException;
		}

		$user->setPassword($password);
		$this->em->flush();
		return TRUE;
	}

	/**
	 * @param $userId
	 * @return bool
	 * @throws UserNotFoundException
	 */
	public function activateAccount($userId)
	{
		/**
		 * @var UserSuperClass $user
		 */
		$user = $this->userRepository()->find($userId);

		if (!$user) {
			throw new UserNotFoundException;
		}

		$user->activate();
		$this->em->flush();
		return TRUE;
	}

	/**
	 * @return EntityRepository
	 */
	private function userRepository() : EntityRepository
	{
		return $this->em->getRepository(UserEntity::class);
	}

}