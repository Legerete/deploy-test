<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		\Tracy\Debugger::log(__METHOD__);
		$this->template->anyVariable = 'any value';
		if (!isset($this->getTemplate()->foo))
		{
			$this->getTemplate()->foo = 'foo';
		}
	}

	public function handleTest()
	{
		\Tracy\Debugger::log(__METHOD__);
		$this->getTemplate()->foo = microtime();
		$this->redrawControl();
	}

	/**
	 * Vyrenderuje robots.txt podle toho, zda bezi aplikace na produkci nebo ne.
	 * Pokud existuje soubor sitemap.xml, pak bude na produkci pridan i zaznam se sitemap.
	 */
	public function renderRobotsTxt() {

		header("Content-type: text/plain; charset=utf-8");
		if ($this->template->isProduction) {
			$text = "User-agent: *\nDisallow: ";
			//if (file_exists($this->sitemapXmlPath))
			//	$text .= sprintf("\nSitemap: %ssitemap.xml", $this->getHttpRequest()->getUrl()->getBaseUrl());
		} else {
			$text = "User-agent: *\nDisallow: /";
		}
		echo $text;
		$this->terminate();
	}

}
