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

class mod_world_of_logs {

    public static function onload(&$params) {
        include_once dirname(__FILE__) . '/class.wol.php';

        $wol = new wol($params->get('guild'));

        $wol->cache_handler = $params->get('wolcache', 'file');
        $wol->cache_timeout = $params->get('wolcachetime', 60);
        $wol->cache_path = JPATH_CACHE . '/';

        $wol->build();

        return $wol; // return the obj, not the content!
    }

}