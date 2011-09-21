<?php

/**
 * World of Logs latest Raids Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 Branko Wilhelm
 * @package    mod_world_of_logs
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @version    $Id$
 */
// no direct accesss
defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet(JURI::base(true) . '/modules/mod_world_of_logs/tmpl/clean.css', 'text/css', 'all');

echo $wol->table('mod_world_of_logs' . $params->get('moduleclass_sfx'), null); // output only the table