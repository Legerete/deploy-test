<?php

namespace Legerete\Security;

use Nette\Security;

/**
 * Users authenticator.
 * @author Petr Horacek <petr.horacek@wunderman.cz>
 */
class DatabaseAuthenticator implements Security\IAuthenticator
{
	/**
	 * @var \Kdyby\Doctrine\EntityManager
	 */
	public $entityManager;

	/**
	 * @author Petr Horacek <petr.horacek@wunderman.cz>
	 * @param \Kdyby\Doctrine\EntityManager $entityManager
	 */
	public function __construct(\Kdyby\Doctrine\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * Performs an authentication.
	 * @author Petr Horacek <petr.horacek@wunderman.cz>
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$user = $this->userRespository()
						->createQueryBuilder('u')
						->select('u')
						->where('u.username = :username')
						->setParameter('username', $username)
						->getQuery()
						->getResult();

		if (!count($user)) {
			throw new Security\AuthenticationException('Špatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
		}

		$user = (object) $user[0];

		/*if (!Passwords::verify($user->password, $user->password)) {
			throw new Security\AuthenticationException('Špatné přihlašovací údaje.', self::INVALID_CREDENTIAL);
		}*/

		if ($user->isDel())
		{
			throw new Security\AuthenticationException('Uživatelský účet byl smazán.', self::IDENTITY_NOT_FOUND);
		}

		if (is_null($user->role) or $user-> role === '')
		{
			throw new \Nette\InvalidArgumentException('Uživatelský účet nemá nastavenou žádnou roli.');
		}

		return new Security\Identity($user->id, $user->role, [
			'name' => $user->getName(),
			'surname' => $user->getSurname(),
			'email' => $user->getEmail(),
			'role' => $user->getRole()
		]);
	}

	private function userRespository()
	{
		return $this->entityManager->getRepository('\App\Entity\User');
	}

}
