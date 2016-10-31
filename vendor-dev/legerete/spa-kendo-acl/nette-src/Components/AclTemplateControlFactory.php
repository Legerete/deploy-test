<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoAcl
 */

namespace Legerete\Spa\KendoAcl\Components;

interface AclTemplateControlFactory
{
	/**
	 * @return AclTemplateControl
	 */
	public function create();
}