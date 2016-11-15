<?php

namespace App\Presenters;

use Nette;
use App\Model;
use OTPHP\TOTP;


class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		$otp = new TOTP(
			'sirbesir@gmail.com',
			'DAFHKF5T3NR65HTPBZXX6M2LPGM7AMFXLWKF3LFBWRIRTXA3SBH5XAVNGOCK3BQ5XR47FT2TZQC7HEDOHABJIFQWENLNSAR5XL2W2BDJCIUBKOEHXKKOFRK57WYIOWIM44L52FH7OVFTGI4DCW4VVWEE5GNGFA4NZWPUMK6S2WLIHI5XYTTY5S6D752UVJJ6YUHHY7MWBYBXT2XY2JZ56JUPGFWIRCMM6D5JVUYIUSD5BGRG34HJQYZ3DQQMSWNC5MAL34OSX5SQKF7RSN23GAF5QJKILVY4WF3G6MVKZV5KCBIDRSAM24IPUWNLIOCLW7VL3O54M62GXRLRNM7QDQNMRWNWVMX65627WJD3BLQDU43AHZSEA7CGCICPNDVKVPEXEOKSR2SOERIBL72HPXWNQI'
		);


		\Tracy\Debugger::barDump($otp->now());
		echo $otp->getProvisioningUri();


		$this->template->anyVariable = 'any value';
		if (!isset($this->getTemplate()->foo))
		{
			$this->getTemplate()->foo = 'foo';
		}
	}

	public function handleTest()
	{
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
