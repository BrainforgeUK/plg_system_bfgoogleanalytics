<?php
/**
 * @package   Plugin for adding Google Analytics to site.
 * @version   0.0.1
 * @author    http://www.brainforge.co.uk
 * @copyright Copyright (C) 2011-2018 Jonathan Brain. All rights reserved.
 * @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.plugin.plugin');

class plgSystemBFGoogleAnalytics extends JPlugin
{
	private $trackingcode;

	function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	function onBeforeRender()
	{
		$this->trackingcode = trim($this->params->get('trackingcode'));
		if (empty($this->trackingcode) || strpos($this->trackingcode, 'UA-00000000') !== false)
		{
			$application = JFactory::getApplication();
			if($application->isAdmin())
			{
				JFactory::getLanguage()->load('plg_system_bfgoogleanalytics');
				$application->enqueueMessage(JText::_('PLG_BFGOOGLEANALYTICS_TRACKINGCODE_ERROR'), 'warning');
			}
			$this->trackingcode = null;
			return true;
		}
	}

	function onAfterRender()
	{
		if (empty($this->trackingcode)) {
			return true;
		}

		if (!empty($_SERVER['SERVER_ADDR']) &&
			$this->params->get('showInDevelopment') != '1')
		{
			if ($_SERVER['SERVER_ADDR'] == @$_SERVER['REMOTE_ADDR'])
			{
				return true;
			}
			if (substr($_SERVER["SERVER_ADDR"], 0, 2) == 'fd' ||
				substr($_SERVER["SERVER_ADDR"], 0, 2) == 'fe')
			{
				return true;
			}
			$baseIP = explode('.', $_SERVER["SERVER_ADDR"]);
			switch ($baseIP[0])
			{
				case '127': return true;
				case '192':
					if ($baseIP[1] == '168') return true;
					break;
			}
		}

		if ($this->params->get('showInAdmin') != '1')
		{
			if(JFactory::getApplication()->isAdmin()) return true;
		}

		$this->buffer = JResponse::getBody();
		$pos = stripos($this->buffer, '<head');
		if ($pos === false) return false;
		$pos = stripos($this->buffer, '>', $pos);
		if ($pos === false) return false;
		$pos += 1;

		$this->buffer = substr($this->buffer, 0, $pos) .
			$this->trackingcode .
			substr($this->buffer, $pos);
		JResponse::setBody($this->buffer);

		return true;
	}
}
?>