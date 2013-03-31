<?php

/**
 * World of Logs latest Raids Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2013 Branko Wilhelm
 * @package    mod_world_of_logs
 * @license    GNU General Public License v3
 * @version    $Id$
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';

$wol = mod_world_of_logs::onload($params);

require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
