<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoScheduler
 */

namespace Legerete\Spa\KendoScheduler\Components;

interface SchedulerTemplateControlFactory
{
	/**
	 * @return SchedulerTemplateControl
	 */
	public function create();
}