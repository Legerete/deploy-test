<?php

	/**
	 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
	 * @author      Petr Besir Horáček <sirbesir@gmail.com>
	 * @package     Legerete\WebSocket
	 *
	 * This file is forked part of the Nette Framework (https://nette.org)
	 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
	 */

	namespace Legerete\WebSocket\Application;

	use Nette;
	use Nette\Application as NetteApplication;
	use Nette\Application\AbortException;
	use Nette\Application\IRouter;
	use Nette\Application\UI;
	use Nette\Application\ApplicationException;
	use Nette\Application\BadRequestException;
	use Nette\Application\InvalidPresenterException;
	use Nette\Application\Request;
	use Nette\Application\Responses;
	use Nette\Http\IRequest;
	use Nette\Application\IPresenter;
	use Nette\Application\IPresenterFactory;


	/**
	 * Front Controller.
	 */
	class Application extends Nette\Application\Application
	{
		use Nette\SmartObject;

		/** @var int */
		public static $maxLoop = 20;

		/** @var bool enable fault barrier? */
		public $catchExceptions;

		/** @var string */
		public $errorPresenter;

		/** @var callable[]  function (Application $sender); Occurs before the application loads presenter */
		public $onStartup;

		/** @var callable[]  function (Application $sender, \Exception|\Throwable $e = NULL); Occurs before the application shuts down */
		public $onShutdown;

		/** @var callable[]  function (Application $sender, Request $request); Occurs when a new request is received */
		public $onRequest;

		/** @var callable[]  function (Application $sender, Presenter $presenter); Occurs when a presenter is created */
		public $onPresenter;

		/** @var callable[]  function (Application $sender, IResponse $response); Occurs when a new response is ready for dispatch */
		public $onResponse;

		/** @var callable[]  function (Application $sender, \Exception|\Throwable $e); Occurs when an unhandled exception occurs in the application */
		public $onError;

		/** @var Request[] */
		private $requests = [];

		/** @var IPresenter */
		private $presenter;

		/** @var Nette\Http\IRequest */
		private $httpRequest;

		/** @var Nette\Http\IResponse */
		private $httpResponse;

		/** @var IPresenterFactory */
		private $presenterFactory;

		/** @var IRouter */
		private $router;


		public function __construct(IPresenterFactory $presenterFactory, IRouter $router, Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
		{
			$this->httpRequest = $httpRequest;
			$this->httpResponse = $httpResponse;
			$this->presenterFactory = $presenterFactory;
			$this->router = $router;
		}


		/**
		 * Dispatch a HTTP request to a front controller.
		 * @return void
		 */
		public function run()
		{
			try {
				$this->onStartup($this);
				$this->processRequest($this->createInitialRequest());
				$this->onShutdown($this);

			} catch (\Throwable $e) {
			} catch (\Exception $e) {
			}
			if (isset($e)) {
				$this->onError($this, $e);
				if ($this->catchExceptions && $this->errorPresenter) {
					try {
						$this->processException($e);
						$this->onShutdown($this, $e);
						return;

					} catch (\Throwable $e) {
						$this->onError($this, $e);
					} catch (\Exception $e) {
						$this->onError($this, $e);
					}
				}
				$this->onShutdown($this, $e);
				throw $e;
			}
		}


		/**
		 * @return Request
		 */
		public function createInitialRequest()
		{
			$request = $this->router->match($this->httpRequest);

			if (!$request instanceof Request) {
				throw new BadRequestException('No route for HTTP request.');

			} elseif (strcasecmp($request->getPresenterName(), $this->errorPresenter) === 0) {
				throw new BadRequestException('Invalid request. Presenter is not achievable.');
			}

			try {
				$name = $request->getPresenterName();
				$this->presenterFactory->getPresenterClass($name);
			} catch (InvalidPresenterException $e) {
				throw new BadRequestException($e->getMessage(), 0, $e);
			}

			return $request;
		}


		/**
		 * @return void
		 */
		public function processRequest(Request $request)
		{
			process:

			// @todo chcem to kontrolovat? pri opakovanem pozadavku na tu samou akci to tu umre, nejspíš by byl stejný problém i kdyby to delali různí uživatelé, chce to vyzkoušet...
//			if (count($this->requests) > self::$maxLoop) {
//				throw new ApplicationException('Too many loops detected in application life cycle.');
//			}

			$this->requests[] = $request;
			$this->onRequest($this, $request);
			$this->presenter = $this->presenterFactory->createPresenter($request->getPresenterName());
			$this->onPresenter($this, $this->presenter);
			$response = $this->presenter->run(clone $request);

			if ($response instanceof Responses\ForwardResponse) {
				$request = $response->getRequest();
				goto process;

			} elseif ($response) {
				$this->onResponse($this, $response);
				$response->send($this->httpRequest, $this->httpResponse);
			}
		}


		/**
		 * @param  \Exception|\Throwable
		 * @return void
		 */
		public function processException($e)
		{
			if (!$e instanceof BadRequestException && $this->httpResponse instanceof Nette\Http\Response) {
				$this->httpResponse->warnOnBuffer = FALSE;
			}
			if (!$this->httpResponse->isSent()) {
				$this->httpResponse->setCode($e instanceof BadRequestException ? ($e->getCode() ?: 404) : 500);
			}

			$args = ['exception' => $e, 'request' => end($this->requests) ?: NULL];
			if ($this->presenter instanceof UI\Presenter) {
				try {
					$this->presenter->forward(":$this->errorPresenter:", $args);
				} catch (AbortException $foo) {
					$this->processRequest($this->presenter->getLastCreatedRequest());
				}
			} else {
				$this->processRequest(new Request($this->errorPresenter, Request::FORWARD, $args));
			}
		}


		/**
		 * Returns all processed requests.
		 * @return Request[]
		 */
		public function getRequests()
		{
			return $this->requests;
		}


		/**
		 * Returns current presenter.
		 * @return IPresenter
		 */
		public function getPresenter()
		{
			return $this->presenter;
		}


		/********************* services ****************d*g**/


		/**
		 * Returns router.
		 * @return IRouter
		 */
		public function getRouter()
		{
			return $this->router;
		}


		/**
		 * Returns presenter factory.
		 * @return IPresenterFactory
		 */
		public function getPresenterFactory()
		{
			return $this->presenterFactory;
		}

		/**
		 * @param IRequest $request
		 */
		public function setHttpRequest(IRequest $request)
		{
			$this->httpRequest = $request;
		}
	}



