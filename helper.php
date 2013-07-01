<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2013 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
 
defined('_JEXEC') or die;

abstract class mod_world_of_logs {

    private static $zones = array(4812 => 'ICC', 4987 => 'RS', 4493 => 'OS', 4273 => 'Ulduar', 4722 => 'PDK', 3456 => 'Nax', 2159 => 'Ony', 4603 => 'VA', 4500 => 'Malygos', 5600 => 'BH', 5334 => 'BoT', 5094 => 'BWD', 5638 => 'T4W', 5723 => 'FL', 5892 => 'DS', 6125 => 'Mogu', 6297 => 'HoF', 6067 => 'ToES', 6622 => 'ToT');
    
    public static function _(JRegistry &$params) {
    	$url = 'http://www.worldoflogs.com/feeds/guilds/' . $params->get('guild') . '/raids/';
    	
    	$cache = JFactory::getCache(__CLASS__ , 'output');
    	$cache->setCaching(1);
    	$cache->setLifeTime($params->get('cache_time', 60));
    	if(!$result = $cache->get($params->get('guild'))) {
    		try {
    			$http = new JHttp(new JRegistry, new JHttpTransportCurl(new JRegistry));
    			$http->setOption('userAgent', 'Joomla! ' . JVERSION . '; World of Logs latest Raids; php/' . phpversion());
    		
	    		$result = $http->get($url, null, $params->get('timeout', 10));
    		}catch(Exception $e) {
    			return $e->getMessage();
    		}
    		
    		$cache->store($result, $params->get('guild'));
    	}
    	
      	if($result->code != 200) {
        	return __CLASS__ . ' HTTP-Status ' . JHtml::_('link', 'http://wikipedia.org/wiki/List_of_HTTP_status_codes#'.$result->code, $result->code, array('target' => '_blank'));
        }
        
        $result->body = json_decode($result->body);
        
        if(empty($result->body->rows) || !is_array($result->body->rows)) {
        	return 'no raids found';
        }
        
    	foreach($result->body->rows as $key => $row) {
    		$row->limit = $row->zones[0]->playerLimit;
    		$row->mode = $row->zones[0]->difficulty;
    		$row->duration = self::duration($row->duration);
    		$row->name = isset(self::$zones[$row->zones[0]->id]) ? self::$zones[$row->zones[0]->id] : $row->zones[0]->name;
    		
    		if($key >= $params->get('raids')) {
    			unset($result->body->rows[$key]);
    			continue;
    		}
    		unset($row->zones, $row->bosses, $row->healingDone, $row->damageDone, $row->damageTaken);
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