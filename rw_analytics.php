<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport('joomla.environment.browser');

class plgSystemRW_analytics extends JPlugin
{
	public function onAfterInitialise()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		// Only add the code when an HTML document is served and not in the Administration
		if ((!$app->isAdmin()) && ($doc->getMimeEncoding() == 'text/html')) {
			$groups = (array) $this->params->get('excluded-groups', null);
			if (!empty($groups)) {
				$userGroups = JFactory::getUser()->get('groups');
				if (!array_intersect($groups, $userGroups)) {
					self::_addTracking();
				}
			} else {
				self::_addTracking();
			}
		}		
	}
	
	private function _addTracking()
	{
		$doc = JFactory::getDocument();
		$browser = JBrowser::getInstance();
		$type = $this->params->get('type');

		if ($browser->isSSLConnection()) {
			$file = 'https://ssl.google-analytics.com/ga.js';
		} else {
			$file = 'http://www.google-analytics.com/ga.js';
		}

		$script = "
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '".htmlspecialchars($this->params->get('account'))."']);";
		if ($type == 1) {
			$script .= "_gaq.push(['_setDomainName', '".htmlspecialchars($this->params->get('domain'))."']);";
		} else if ($type == 2) {
			$script .= "
_gaq.push(['_setDomainName', 'none']);
_gaq.push(['_setAllowLinker', true]);";
		}
		if ($this->params->get('anoymizeip')) {
			$script .= "_gaq.push (['_gat._anonymizeIp']);";
		}
		$script .= "_gaq.push(['_trackPageview']);\n";

		$doc->addScriptDeclaration($script);
		$doc->addScript($file, 'text/javascript');
	}	
}
?>