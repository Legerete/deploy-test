<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Presenters;

use Nette\Http\IRequest;
use Nette\Security\AuthenticationException;
use App;

/**
 * Base presenter for all secured application presenters.
 */
class SecuredPresenter extends BasePresenter
{

	/**
	 * @inject
	 * @var \Kdyby\Doctrine\EntityManager
	 */
	public $em;

	/**
	 * @inject
	 * @var IRequest
	 */
	public $request;

	public function checkRequirements($element)
	{
		parent::checkRequirements($element);

		if (! $this->getUser()->isAllowed($this->getName(), $this->action)) {
			if (! $this->getUser()->isLoggedIn()) {
				if ($this instanceof App\PrivateModule\PrivatePresenter) {
					$this->redirect(':Private:Users:Sign:in', ['backlink' => $this->getPresenter()->storeRequest()]);
				} else {
					$this->redirect(':Public:Users:Sign:in', ['backlink' => $this->getPresenter()->storeRequest()]);
				}
			} else {
				throw new AuthenticationException('Nemáte dostatečná práva pro tuto akci!');
			}

		}
	}


	public function beforeRender()
	{
		parent::beforeRender();
		$this->getTemplate()->identity = $this->getUser()->getIdentity();

		$this->getTemplate()->addFilter('addHttp', function ($url) {
			if (! preg_match("~^(?:ftp|http)s?://~i", $url)) {
				$url = "http://" . $url;
			}
			return $url;
		});
	}
}
