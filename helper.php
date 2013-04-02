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

jimport('joomla.client.http');

abstract class mod_world_of_logs {

    private static $zones = array(4812 => 'ICC', 4987 => 'RS', 4493 => 'OS', 4273 => 'Ulduar', 4722 => 'PDK', 3456 => 'Nax', 2159 => 'Ony', 4603 => 'VA', 4500 => 'Malygos', 5600 => 'BH', 5334 => 'BoT', 5094 => 'BWD', 5638 => 'T4W', 5723 => 'FL', 5892 => 'DS', 6125 => 'Mogu', 6297 => 'HoF', 6067 => 'ToES', 6622 => 'ToT');
    
    public static function _(JRegistry &$params) {

    	if (!$params->get('guild')) {
    		return array('please configure Module' . ' - ' . __CLASS__);
    	}
    	
    	$url = 'http://www.worldoflogs.com/feeds/guilds/' . $params->get('guild') . '/raids/';
    	
    	$cache = JFactory::getCache(__CLASS__ , 'output');
    	$cache->setCaching(1);
    	$cache->setLifeTime($params->get('cache_time', 60) * 60);
    	
    	if(!$result = $cache->get($params->get('guild'))) {
    		$http = new JHttp();
    		$http->setOption('userAgent', 'Joomla! ' . JVERSION . '; World of Logs latest Raids; php/' . phpversion());
    		$result = $http->get($url, null, $params->get('timeout', 10));
    		$cache->store($result, $params->get('guild'));
    	}
    	
    	$cache->setCaching(JFactory::getConfig()->get('caching'));
    	
       if($result->code != 200) {
            return 'error in <strong>' . __CLASS__ . '</strong><br/>HTTP Code: ' . $result->code;
        }
        
        $result->body = json_decode($result->body);
        
        if(empty($result->body->rows) || !is_array($result->body->rows)) {
        	return 'no raids found';
        }
   		
    	foreach($result->body->rows as $row) {
    		$row->limit = $row->zones[0]->playerLimit;
    		$row->mode = $row->zones[0]->difficulty;
    		$row->duration = self::duration($row->duration);
    		$row->name = isset(self::$zones[$row->zones[0]->id]) ? self::$zones[$row->zones[0]->id] : $row->zones[0]->name;
    		
    		unset($row->zones, $row->participants, $row->bosses, $row->healingDone, $row->damageDone, $row->damageTaken, $row->date);
    	}
    	
    	return $result->body->rows;
    }
        
    private static function duration($msec) {
    	$hour = (int) ($msec / 1000 / 60 / 60);
    	$msec = $msec - $hour * 60 * 60 * 1000;
    	$min = (int) ($msec / 1000 / 60);
    	$msec = $msec - $min * 60 * 1000;
    	$sec = (int) ($msec / 1000);
    	return $hour . ':' . $min;
    }
}