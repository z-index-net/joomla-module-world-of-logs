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

abstract class mod_world_of_logs {

    private static $zones = array(4812 => 'ICC', 4987 => 'RS', 4493 => 'OS', 4273 => 'Ulduar', 4722 => 'PDK', 3456 => 'Nax', 2159 => 'Ony', 4603 => 'VA', 4500 => 'Malygos', 5600 => 'BH', 5334 => 'BoT', 5094 => 'BWD', 5638 => 'T4W', 5723 => 'FL', 5892 => 'DS', 6125 => 'Mogu', 6297 => 'HoF', 6067 => 'ToES', 6622 => 'ToT');
    
    public static function _(JRegistry &$params) {

    	if (!$params->get('guild')) {
    		return array('please configure Module' . ' - ' . __CLASS__);
    	}
    
    	$url = 'http://www.worldoflogs.com/feeds/guilds/' . $params->get('guild') . '/raids/';
    	
    	$cache = JFactory::getCache(__CLASS__);
    	$cache->setCaching(1);
    	$cache->setLifeTime($params->get('cache_time', 60) * 60);
    	
    	$result = $cache->call(array(__CLASS__, 'curl'), $url, $params->get('timeout', 10));
    	
    	$cache->setCaching(JFactory::getConfig()->get('caching'));
    	
       if(!is_object($result['body']) || $result['info']['http_code'] != 200) {
            $err[] = '<strong>error during request</strong>';
            if($result['errno'] != 0) {
                $err[] = 'Error: ' . $result['error'] . ' (' . $result['errno'] . ')';
            }
            $err[] = 'URL: ' . JHTML::link($url, $guild);
            $err[] = 'HTTP Code: ' . $result['info']['http_code'];
            return implode('<br/>', $err);
        }
        
        if(empty($result['body']->rows)) {
        	return 'no raids found';
        }
   		
    	foreach($result['body']->rows as $row) {
    		$row->limit = $row->zones[0]->playerLimit;
    		$row->mode = $row->zones[0]->difficulty;
    		$row->duration = self::duration($row->duration);
    		$row->name = isset(self::$zones[$row->zones[0]->id]) ? self::$zones[$row->zones[0]->id] : $row->zones[0]->name;
    		
    		unset($row->zones, $row->participants, $row->bosses, $row->healingDone, $row->damageDone, $row->damageTaken, $row->date);
    	}
    	
    	return $result['body']->rows;
    }
        
    private static function duration($msec_total) {
    	$hour = (int) ($msec_total / 1000 / 60 / 60);
    	$msec_total = $msec_total - $hour * 60 * 60 * 1000;
    	$min = (int) ($msec_total / 1000 / 60);
    	$msec_total = $msec_total - $min * 60 * 1000;
    	$sec = (int) ($msec_total / 1000);
    	return "$hour:$min";
    }    
        
	public static function curl($url, $timeout=10) {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Joomla! ' . JVERSION . '; World of Logs latest Raids; php/' . phpversion());
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close'));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	
		$body = curl_exec($curl);
		$info = curl_getinfo($curl);
		$errno = curl_errno($curl);
		$error = curl_error($curl);
		
		$body = json_decode($body);
	
		curl_close($curl);
	
		return array('info' => $info, 'errno' => $errno, 'error' => $error, 'body' => $body);
	}
}