<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

if (!class_exists('WoWModuleAbstract')) {
    echo JText::_('JERROR_ERROR') . ': WoW-Plugin not found?!';
    return;
}

JLoader::register('ModWorldOfLogsHelper', dirname(__FILE__) . '/helper.php');

$logs = ModWorldOfLogsHelper::getData($params);

if (!$params->get('ajax') && !is_array($logs)) {
    echo $logs;
    return;
}

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));