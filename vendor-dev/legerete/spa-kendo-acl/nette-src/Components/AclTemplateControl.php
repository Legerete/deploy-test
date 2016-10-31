<?php

/**
 * @copyright   Copyright (c) 2016 Legerete s.r.o. <core@legerete.cz>
 * @author      Petr Besir Horáček <sirbesir@gmail.com>
 * @package     Legerete\SpaKendoAcl
 */

namespace Legerete\Spa\KendoAcl\Components;

use Legerete\Spa\KendoAcl\Model\Service\AclModelService;
use Nette\Application\UI\Control;
use Nette\Utils\Json;

class AclTemplateControl extends Control
{

	/**
	 * @var AclModelService $modelService
	 */
	private $modelService;

	/**
	 * AclTemplateControl constructor.
	 *
	 * @param AclModelService $modelService
	 */
	public function __construct(AclModelService $modelService)
	{
		$this->modelService = $modelService;
	}

	public function render()
	{
		$this->getTemplate()->setFile(__DIR__.'/templates/spa-acl.latte');
		$this->getTemplate()->resources = $this->modelService->getResources();
		$this->getTemplate()->allPrivileges = $this->modelService->getAllPrivilegesOfResources();
		$this->getTemplate()->allResourcesJson = $this->preparePrivilegesJson();
		$this->getTemplate()->render();
	}

	/**
	 * @return string JSON encoded assoc array with list of resource=>privileges tree for use in JS
	 */
	private function preparePrivilegesJson()
	{
		$resources = $this->modelService->getResources();
		$result = [];

		foreach ($resources as $resourceKey => $privileges) {
			$newResKey = str_replace(':', '', $resourceKey);
			$result[$newResKey] = [];
			foreach ($privileges as $privilegeKey => $value) {
				$result[$newResKey][$privilegeKey] = false;
			}
		}

		return Json::encode($result);
	}
}