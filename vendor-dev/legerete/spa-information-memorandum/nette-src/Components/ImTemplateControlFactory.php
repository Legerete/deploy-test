<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoIm
 */

namespace Legerete\Spa\KendoIm\Components;

interface ImTemplateControlFactory
{
	/**
	 * @return ImTemplateControl
	 */
	public function create();
}