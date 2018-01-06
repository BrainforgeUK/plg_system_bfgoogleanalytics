<?php
/**
* @package   Plugin for adding Google Analytics to site.
* @version   0.0.1
* @author    http://www.brainforge.co.uk
* @copyright Copyright (C) 2011-2017 Jonathan Brain. All rights reserved.
* @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/
 
// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.plugin.plugin');

class plgSystemBFGoogleAnalytics extends JPlugin {
  function __construct(&$subject, $config) {
    parent::__construct($subject, $config);
  }

  function onAfterRender() {
    if (!empty($_SERVER['SERVER_ADDR']) &&
	    $this->params->get('showInDevelopment') != '1') {
      if ($_SERVER['SERVER_ADDR'] == @$_SERVER['REMOTE_ADDR']) {
        return true;
      }
      if (substr($_SERVER["SERVER_ADDR"], 0, 2) == 'fd' ||
        substr($_SERVER["SERVER_ADDR"], 0, 2) == 'fe') {
        return true;
      }
      $baseIP = explode('.', $_SERVER["SERVER_ADDR"]);
      switch ($baseIP[0]) {
        case '127': return true;
        case '192':
          if ($baseIP[1] == '168') return true;
          break;
      }
    }

    $googleUAID = $this->params->get('googleUAID');
    if ($googleUAID == null ||
        strpos($_SERVER["PHP_SELF"], "index.php") === false) {
      return true;
    }
    
    if ($this->params->get('showInAdmin') != '1') {
  		$app = JFactory::getApplication();
	   	if($app->isAdmin()) return true;
    }
    
    $targetDomain = trim($this->params->get('targetDomain'));
    if (!empty($targetDomain) && !empty($_SERVER['HTTP_HOST'])) {
      $host = $_SERVER['HTTP_HOST'];
      if (strpos($host, 'www.') === 0) {
        $host = substr($host, 4);
      }
      if ($host != $targetDomain) return true;
    }

    $php_condition = trim($this->params->get('php_condition'), " ;\t\n\r\0\x0B");
    if (!empty($php_condition)) {
      if (!eval('return ' . $php_condition . ';')) {
        return true;
      }
    }

    $ip_block_list = trim( $this->params->get('ip_block_list'));
    foreach (preg_split( '/[,\s]+/', $ip_block_list) as $value)	{
      if (empty($value)) continue;
      if (preg_match('/^' . $value . '$/', FOFUtilsIp::getIp())) {
        return true;
      }
    }

    if (empty($domainName)) $domainName = str_replace('www.', '', $_SERVER['HTTP_HOST']);

    $script_top = 'var _gaq = _gaq || []; _gaq.push([\'_setAccount\', \'' . $googleUAID . '\']);';
    $domainName = $this->params->get('domainName');
    if (!empty($domainName)) $script_top .= '_gaq.push([\'_setDomainName\', \'' .  $domainName . '\']);';
    if ($this->params->get('multiTopLevel')) $script_top .= '_gaq.push([\'_setAllowLinker\', true]);';
    $script_top .= '_gaq.push([\'_trackPageview\']);';

    $script_bottom = '(function() {
  var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
  ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
  var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
})();';

    $javascriptPosition = $this->params->get('javascriptPosition');
    $this->buffer = JResponse::getBody();
    switch ($javascriptPosition) {
      case 'head';
        if (!$this->insertGoogleCode(true, $javascriptPosition, $script_top . "\n" . $script_bottom)) return true;
        break;
      case 'body';
        if (!$this->insertGoogleCode(false, $javascriptPosition, $script_top . "\n" . $script_bottom)) return true;
        break;
      case 'SPLIT';
        if (!$this->insertGoogleCode(true, 'head', $script_top)) return true;
        if (!$this->insertGoogleCode(false, 'body', $script_bottom)) return true;
        break;
      default:
        return true;
    }
    JResponse::setBody($this->buffer);
    return true;
  }

  private function insertGoogleCode($reverse, $javascriptPosition, $googleCode) {
    $javascriptPosition = '</'. $javascriptPosition . '>';
    if ($reverse) $pos = strripos($this->buffer, $javascriptPosition);
    else $pos = stripos($this->buffer, $javascriptPosition);
    if ($pos === false) return false;
    
    $this->buffer = substr($this->buffer, 0, $pos) . '
<script type="text/javascript">' . $googleCode . '</script>
' . substr($this->buffer, $pos);
    return true;
  }
}
?>