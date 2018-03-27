<?php
/**
 * @package   Plugin for adding Google Analytics to site.
 * @version   0.0.1
 * @author    http://www.brainforge.co.uk
 * @copyright Copyright (C) 2011-2018 Jonathan Brain. All rights reserved.
 * @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('textarea');

/**
 * Form Field class for the Joomla Platform.
 * Supports a multi line area for entry of html content
 */
class JFormFieldHtmltextarea extends JFormFieldTextarea
{
	/**
	 * Method to get the textarea field input markup.
	 * Use the rows and columns attributes to specify the dimensions of the area.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$html = parent::getInput();
		$html .= '<br/>' . jText::_("PLG_BFGOOGLEANALYTICS_TRACKINGCODE_INFO") . '<br/>';
		$html .= '<img src="' . Juri::root() . 'plugins/system/bfgoogleanalytics/images/googletrackingcode.jpg"/>';
		
		return $html;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$data['value'] = trim($data['value']);
		if (empty($data['value'])) {
			$data['value'] = "<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id=UA-00000000-0\"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-00000000-0');
</script>";
		}

		return $data;
	}
}
