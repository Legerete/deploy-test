<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoUser
 */

namespace Legerete\Spa\KendoUser\Components;

interface UserTemplateControlFactory
{
	/**
	 * @return UserTemplateControl
	 */
	public function create();
}