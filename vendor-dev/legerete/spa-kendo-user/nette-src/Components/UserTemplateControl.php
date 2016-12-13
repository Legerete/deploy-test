<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoUser
 */

namespace Legerete\Spa\KendoUser\Components;

use Legerete\Spa\KendoUser\Model\Service\UserModelService;
use Nette\Application\UI\Control;

class UserTemplateControl extends Control
{
	/**
	 * @var UserModelService $userModelService
	 */
	private $userModelService;

	/**
	 * UserTemplateControl constructor.
	 *
	 * @param UserModelService $userModelService
	 */
	public function __construct(UserModelService $userModelService)
	{
		$this->userModelService = $userModelService;
	}


	public function render()
	{
		$this->getTemplate()->userAllowedColors = ($this->userModelService)::USER_ALLOWED_COLLORS;
		$this->getTemplate()->setFile(__DIR__.'/templates/spa-user.latte');
		$this->getTemplate()->render();
	}
}