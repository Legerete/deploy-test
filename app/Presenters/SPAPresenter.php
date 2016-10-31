<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\Spa
 */

namespace App\Presenters;

use Legerete\Spa\Collection\SpaTemplatesControlsCollection;
use Nette\Security\IAuthorizator;
use Ublaboo\ImageStorage\ImageStorage;

class SPAPresenter extends BasePresenter
{
	/**
	 * @var ImageStorage
	 * @inject
	 */
	public $imageStorage;

	/**
	 * @var IAuthorizator $permissions
	 * @inject
	 */
	public $permissions;

	/**
	 * @var SpaTemplatesControlsCollection
	 * @inject
	 */
	public $spaTemplates;

	public function startup()
	{
		parent::startup();
		$this->getTemplate()->imageStorage = $this->imageStorage;
		$this->getTemplate()->templatesControls = $this->spaTemplates->getKeys();
	}

	public function createComponent($name)
	{
		return $this->spaTemplates->get($name)->create();
	}
}
