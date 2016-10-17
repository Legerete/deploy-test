<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Security;

use Legerete\User\Entity\UserEntity;
use Nette\Security\AuthenticationException;
use Nette\InvalidArgumentException;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\Passwords;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\User;

final class DatabaseAuthenticator implements IDatabaseAuthenticator
{

	/**
	 * @var EntityManager
	 */
	private $em;

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @param EntityManager $em
	 * @param User $user
	 */
	public function __construct(EntityManager $em, User $user)
	{
		$this->em = $em;
		$this->user = $user;
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @param bool $authenticateUser If is set to FALSE user will be logged, but no athenticate (set-up user identity without login)
	 * @return void
	 * @throws AuthenticationException
	 * @throws InvalidArgumentException
	 */
	public function authenticate($username, $password, $authenticateUser = TRUE)
	{
		$user = $this->userRespository()
			->createQueryBuilder('u')
			->select('u')
			->where('u.username = :username')
			->setParameter('username', $username)
			->getQuery()
			->getSingleResult();

		if (!$user) {
			throw new AuthenticationException(
				'Špatné přihlašovací údaje.',
				IAuthenticator::INVALID_CREDENTIAL
			);
		}

		if (!Passwords::verify($password, $user->password)) {
			throw new AuthenticationException(
				'Špatné přihlašovací údaje.',
				IAuthenticator::INVALID_CREDENTIAL
			);
		}

		if ($user->isDel())
		{
			throw new AuthenticationException(
				'Uživatelský účet byl smazán.',
				IAuthenticator::IDENTITY_NOT_FOUND
			);
		}

		if (is_null($user->role) or $user->role === '')
		{
			throw new InvalidArgumentException('Uživatelský účet nemá nastavenou žádnou roli.');
		}

		$identity = new Identity($user->id, $user->role, [
			'name' => $user->getName(),
			'surname' => $user->getSurname(),
			'email' => $user->getEmail(),
			'role' => $user->getRole()
		]);

		$this->user->login($identity);

		if ($authenticateUser === FALSE) {
			$this->user->getStorage()->setAuthenticated(FALSE);
		}
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	protected function userRespository()
	{
		return $this->em->getRepository(UserEntity::class);
	}

}