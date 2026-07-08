<?php
/**
 * @package   Plugin for adding Google Analytics to site.
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2011-2026 Jonathan Brain. All rights reserved.
 * @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

// no direct access
defined('_JEXEC') or die;

class plgSystemBFGoogleAnalytics extends CMSPlugin
{
	protected $app;
	protected $trackingcodes;
	protected $measurementID0;

	/*
	 */
	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->app = Factory::getApplication();

		$this->trackingcodes = (array)$this->params->get('trackingcodes', array());
		if (!empty($this->trackingcodes))
		{
			$this->measurementID0 = trim(reset($this->trackingcodes)->measurementid);
			if (!preg_match('/^[A-Z]+[-A-Za-z0-9]+$/', $this->measurementID0))
			{
				$this->measurementID0 = null;
			}
		}
	}

	/*
	 */
	function onBeforeRender()
	{
		if (empty($this->measurementID0) || $this->measurementID0 == 'UA-00000000-0')
		{
			$this->trackingcodes = null;

			if($this->app->isClient('administrator'))
			{
				switch($this->app->getInput()->getCmd('option'))
				{
					case 'com_cpanel':
					case 'com_installer':
					case 'com_plugins':
						$this->app->getLanguage()->load('plg_system_bfgoogleanalytics.sys', __DIR__);
						$this->app->enqueueMessage(Text::_('PLG_SYSTEM_BFGOOGLEANALYTICS_TRACKINGCODE_ERROR'), 'warning');
						break;
					default:
						break;
				}
			}
		}
	}

	/*
	 */
	function onAfterRender()
	{
		if (empty($this->measurementID0)) {
			return;
		}

		if ($this->app->isClient('administrator'))
		{
			if ($this->params->get('showInAdmin') != '1')
			{
				return;
			}
		}
		else if (!$this->app->isClient('site'))
		{
			return;
		}

		if (!empty($_SERVER['SERVER_ADDR']) &&
			$this->params->get('showInDevelopment') != '1')
		{
			if ($_SERVER['SERVER_ADDR'] == @$_SERVER['REMOTE_ADDR'])
			{
				return;
			}
			if (substr($_SERVER["SERVER_ADDR"], 0, 2) == 'fd' ||
				substr($_SERVER["SERVER_ADDR"], 0, 2) == 'fe')
			{
				return;
			}
			$baseIP = explode('.', $_SERVER["SERVER_ADDR"]);
			switch ($baseIP[0])
			{
				case '127': return;
				case '192':
					if ($baseIP[1] == '168') return;
					break;
			}
		}

		$gaCode = '
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=' . $this->measurementID0 . '"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(\'js\', new Date());
';

		foreach($this->trackingcodes as $trackingcode)
		{
			$gaCode .= "  gtag('config', '" . trim($trackingcode->measurementid) . "');\n";
		}

		$gaCode .= "</script>\n";

		$buffer = $this->app->getBody();

		$pos = stripos($buffer, '<head');
		if ($pos === false) return;
		$pos = stripos($buffer, '>', $pos);
		if ($pos === false) return;
		$pos += 1;

		$buffer = substr($buffer, 0, $pos) . $gaCode . substr($buffer, $pos);

		$this->app->setBody($buffer);
	}
}
?>