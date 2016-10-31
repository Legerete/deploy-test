<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoUser
 */

namespace Legerete\Spa\KendoUser\Components;

use Nette\Application\UI\Control;

class UserTemplateControl extends Control
{
	public function render()
	{
		$this->getTemplate()->setFile(__DIR__.'/templates/spa-user.latte');
		$this->getTemplate()->render();
	}
}