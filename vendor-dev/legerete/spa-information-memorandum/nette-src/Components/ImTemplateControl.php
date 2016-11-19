<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoIm
 */

namespace Legerete\Spa\KendoIm\Components;

use Nette\Application\UI\Control;

class ImTemplateControl extends Control
{
	public function render()
	{
		$this->getTemplate()->setFile(__DIR__.'/templates/spa-information-memorandum.latte');
		$this->getTemplate()->render();
	}
}