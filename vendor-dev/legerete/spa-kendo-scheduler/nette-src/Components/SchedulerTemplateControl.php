<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoScheduler\Components;

use Nette\Application\UI\Control;

class SchedulerTemplateControl extends Control
{
	public function render()
	{
		$this->getTemplate()->setFile(__DIR__.'/templates/spa-scheduler.latte');
		$this->getTemplate()->render();
	}
}