<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Legerete\Security
 */

namespace Legerete\Security;

use Doctrine\ORM\NoResultException;
use Legerete\Security\Model\Entity\UserEntity;
use Nette\Localization\ITranslator;
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
	 * @var EntityManager $em
	 */
	private $em;

	/**
	 * @var User $user
	 */
	private $user;

	/**
	 * @var ITranslator $translator
	 */
	private $translator;

	/**
	 * @param EntityManager $em
	 * @param User $user
	 */
	public function __construct(EntityManager $em, User $user, ITranslator $translator)
	{
		$this->em = $em;
		$this->user = $user;
		$this->translator = $translator;
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
		try {
			/**
			 * @var UserEntity $user
			 */
			$user = $this->userRespository()
				->createQueryBuilder('u')
				->select('u')
				->where('u.username = :username')
				->setParameter('username', $username)
				->getQuery()
				->getSingleResult();
		} catch (NoResultException $e) {
			try {
				/**
				 * @var UserEntity $user
				 */
				$user = $this->userRespository()
					->createQueryBuilder('u')
					->select('u')
					->where('u.email = :email')
					->setParameter('email', $username)
					->getQuery()
					->getSingleResult();
			} catch (NoResultException $e) {
				$user = NULL;
			}
		}

		if (! $user) {
			throw new AuthenticationException(
				$this->translator->translate('security.user.invalid-credentials'),
				IAuthenticator::INVALID_CREDENTIAL
			);
		}

		if (! Passwords::verify($password, $user->password)) {
			throw new AuthenticationException(
				$this->translator->translate('security.user.invalid-credentials'),
				IAuthenticator::INVALID_CREDENTIAL
			);
		}

		if ($user->isDel())
		{
			throw new AuthenticationException(
				$this->translator->translate('security.user.account-deleted'),
				IAuthenticator::IDENTITY_NOT_FOUND
			);
		}

		if (! $user->getRoles()->count())
		{
			throw new InvalidArgumentException($this->translator->translate('security.user.no-roles'));
		}

		$userRoles = [AuthorizatorFactory::ROLE_GUEST];
		if ($user->getIsAdmin()){
			$userRoles[] = AuthorizatorFactory::ROLE_ADMIN;
		}

		foreach ($user->getRoles() as $role) {
			$userRoles[] = $role->name;
		}

		$identity = new Identity($user->id, $userRoles, [
			'name' => $user->getName(),
			'surname' => $user->getSurname(),
			'otp' => $user->getOtp(),
			'email' => $user->getEmail(),
			'avatar' => $user->getAvatar()
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