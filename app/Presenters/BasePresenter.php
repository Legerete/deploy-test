<?php

namespace App\Presenters;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \Legerete\Presenters\BasePresenter
{
	public function startup()
	{
		parent::startup();
		$this->getTemplate()->copyrightYear = date('Y');
	}

}
