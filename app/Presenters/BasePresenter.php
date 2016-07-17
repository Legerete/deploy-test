<?php

namespace App\Presenters;

use Nette;
use App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/**
	 * @var \Nette\DI\Container
	 * @inject
	 */
	public $container;

	/**
	 * Get information from configuration, if it is devel or production environment
	 * @return bool
	 */
	protected function isProduction()
	{
		$params = $this->container->getParameters();
		return !isset($params['devel']) || !$params['devel'];
	}

	public function startup()
	{
		parent::startup();
		$this->template->isProduction = $this->isProduction();
		$this->template->copyrightYear = date('Y');
	}

}
