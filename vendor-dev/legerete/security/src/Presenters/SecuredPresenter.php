<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SignInExtension
 */

namespace Legerete\Security\Presenters;

use Legerete\Presenters\BasePresenter;
use Nette\DI\PhpReflection;
use Nette\Http\IRequest;
use Nette\Http\Response;
use Nette\Security\AuthenticationException;
use App;

/**
 * Base presenter for all secured application presenters.
 */
class SecuredPresenter extends BasePresenter
{
	/**
	 * @internal
	 */
	public $resource;

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

		$privilegesAnnotation = PhpReflection::parseAnnotation($element, 'privileges');
		$privileges = explode('|', $privilegesAnnotation);

		if ($element instanceof \Nette\Application\UI\ComponentReflection) {
			$this->resource = PhpReflection::parseAnnotation($element, 'resource');
		}

		foreach ($privileges as $privilege) {
			if ($this->getUser()->isAllowed($this->resource, $privilege))
			{
				return TRUE;
			}
		}

		if ($this->getUser()->isAllowed($this->resource, $this->action)) {
			return TRUE;
		}

		if (! $this->getUser()->isLoggedIn() && $this->action !== 'in') {
			$this->redirect(':LeSignIn:UserSignIn:Sign:in', ['backlink' => $this->getPresenter()->storeRequest()]);
		} elseif ($this->getUser()->isLoggedIn()) {
			if ($this->isAjax())
			{
				$this->sendForbiddenResponse();
			}
			throw new AuthenticationException('Nemáte dostatečná práva pro tuto akci!');
		}
	}

	/**
	 * Send Json forbidden response
	 */
	public function sendForbiddenResponse()
	{
		$this->getHttpResponse()->setCode(Response::S403_FORBIDDEN);
		$this->sendJson([
			'status' => 'error',
			'error' => 'You don\'t have permissions to process this request.'
		]);
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
