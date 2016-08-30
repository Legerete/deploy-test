<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Core
 */

namespace Legerete\UIForm;

use Nette\SmartObject;
use Nette\Application\UI\Form;


class FormFactory
{
	use SmartObject;

	/**
	 * @return Form
	 */
	public function create()
	{
		return new Form;
	}

}
