<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Core
 */

namespace Legerete\Presenters;

use Nette\Application\UI\Presenter;


/**
 * Base presenter for all application presenters.
 */
class BasePresenter extends Presenter
{
	public $pageTitle;

	public function beforeRender()
	{
		$this->getTemplate()->pageTitle = $this->pageTitle;
	}
}
