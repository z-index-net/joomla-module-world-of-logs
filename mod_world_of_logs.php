<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
 
defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/helper.php';

$logs = mod_world_of_logs::_($params);

if(!is_array($logs)) {
	echo $logs;
	return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));