<?php

namespace App\Presenters;



use Ublaboo\ImageStorage\ImageStorage;

class SPAPresenter extends BasePresenter
{
	/**
	 * @var ImageStorage
	 * @inject
	 */
	public $imageStorage;

	public function startup()
	{
		parent::startup();
		$this->getTemplate()->imageStorage = $this->imageStorage;
	}
}
