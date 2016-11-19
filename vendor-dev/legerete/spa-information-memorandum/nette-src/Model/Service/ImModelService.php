<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoIm\Model\Service;

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

class ImModelService
{

	/**
	 * @var EntityManager $em
	 */
	private $em;

	/**
	 * ImModelService constructor.
	 *
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}
}