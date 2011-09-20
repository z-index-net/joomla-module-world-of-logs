<?php

/**
 * World of Logs Parser - PHP-Curl and PHP 5.2 or higher is required!
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2010 - 2011 Branko Wilhelm
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @package    wol_parser
 * @version    $Id: class.wol.php 2 2011-09-20 22:32:09Z bRunO $
 */
class WOL {

    /**
     * @var integer socket timeout in seconds
     */
    public $curl_timeout = 5;

    /**
     * @var object cache handler
     */
    public $cache = null;

    /**
     * @var array possible cache handler
     */
    public $cache_handler = array('Memcache', 'XCache', 'APC', 'File');

    /**
     * @var string cache path for file cache if used
     */
    public $cache_path = './cache/';

    /**
     * @var integer cache timeout to re-create
     */
    public $cache_timeout = 60;

    /**
     * @var array errors
     */
    private $errors = array();

    /**
     * @var array the world of logs data array
     */
    private $json = array();

    /**
     * @var integer world of logs guild id
     */
    private $guild = null;

    /**
     * @var array Zone IDs and Acronyms
     */
    public $zones = array(4812 => 'ICC', 4987 => 'RS', 4493 => 'OS', 4273 => 'Ulduar', 4722 => 'PDK', 3456 => 'Nax', 2159 => 'Ony', 4603 => 'VA', 4500 => 'Malygos', 5600 => 'BH', 5334 => 'BoT', 5094 => 'BWD', 5638 => 'T4W', 5723 => 'FL');

    public function __construct($guild) {

        $this->guild = $guild;

        if (!function_exists('curl_init')) {
            $this->errors[] = 'function "curl_init" does not exists';
        }
    }

    /**
     * select storage handler
     *
     * @return void
     */
    private function set_storage() {
        if (!is_array($this->cache_handler) && !empty($this->cache_handler)) {
            $this->cache_handler = array($this->cache_handler);
        }

        foreach ($this->cache_handler as $handler) {
            if ((extension_loaded($handler) || $handler == 'File') && class_exists('WoLStorage' . $handler)) {
                $handler = 'WoLStorage' . $handler;
                $this->cache = new $handler;
                break;
            }
        }

        if ($this->cache === null && class_exists('WoLStorageFile')) {
            $this->cache = new WoLStorageFile;
        }
        elseif ($this->cache === null) {
            $this->errors[] = 'no cache handler found..';
            return;
        }
        $this->cache_handler = get_class($this->cache);
        if ($this->cache_handler == 'WoLStorageFile') {
            $this->cache->path = $this->cache_path;
        }

        $this->cache->timeout = $this->cache_timeout;
    }

    /**
     * check cache and (re)build the data array
     *
     * @return bool
     */
    public function build() {

        $this->set_storage();

        $this->cache->key = 'wol.' . $this->guild;

        $cache = $this->cache->get();

        if ($cache !== false && is_array($cache) && !empty($cache)) {
            $this->json = $cache;
            return true;
        }

        if ($this->refresh() === false) {
            $this->errors[] = 'no JSON Data response from WoL Server';
            return false;
        }

        return $this->cache->set($this->json);
    }

    /**
     * @return mixed list only tbody to insert in an <table>
     */
    public function tbody() {
        if ($this->error_handler()) {
            return false;
        }

        if (empty($this->json)) {
            $this->errors[] = 'no JSON Data';
            return false;
        }

        $table = "<tbody>\n";

        foreach ($this->json['rows'] as $row) {

            $raid = $row['zones'][0]['name'];
            if (isset($this->zones[$row['zones'][0]['id']]))
                $raid = $this->zones[$row['zones'][0]['id']];

            $limit = $row['zones'][0]['playerLimit'];
            $mode = $row['zones'][0]['difficulty'];

            $time = explode(' ', $row['dateString']);
            $time = explode('-', $time[0]);

            $raid = '<a href="http://www.worldoflogs.com/reports/' . $row['id'] . '/" target="_blank">' . $time[2] . '.' . $time[1] . ' ' . $raid . '</a> <span>(' . $limit . $mode . ")</span>";

            $table .= "<tr>\n";
            $table .= '<td class="raid">' . $raid . "</td>\n";
            $table .= '<td class="duration">' . $this->duration($row['duration']) . "</td>\n";
            $table .= '<td class="bossCount">' . $row['bossCount'] . "</td>\n";
            $table .= '<td class="killCount">' . $row['killCount'] . "</td>\n";
            $table .= '<td class="wipeCount">' . $row['wipeCount'] . "</td>\n";

            $table .= "</tr>\n";
        }
        return $table . "</tbody>\n";
    }

    /**
     * @return mixed list as html table
     */
    public function table($class='wol', $id="wol") {
        $class = $class ? ' class="' . $class . '"' : '';
        $id = $id ? ' id="' . $id . '"' : '';
        return '<table' . $class . $id . '>' . $this->tbody() . "</table>\n";
    }

    /**
     * @return duration time for raid
     */
    private function duration($msec_total) {
        $hour = (int) ($msec_total / 1000 / 60 / 60);
        $msec_total = $msec_total - $hour * 60 * 60 * 1000;
        $min = (int) ($msec_total / 1000 / 60);
        $msec_total = $msec_total - $min * 60 * 1000;
        $sec = (int) ($msec_total / 1000);
        return "$hour:$min";
    }

    /**
     * refresh data from wol server
     *
     * @return bool
     */
    private function refresh() {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'http://www.worldoflogs.com/feeds/guilds/' . $this->guild . '/raids/');
        curl_setopt($curl, CURLOPT_USERAGENT, 'World of Logs PHP Parser; php/' . phpversion());
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Connection: Close'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->curl_timeout);
        $html = curl_exec($curl);
        curl_close($curl);

        if ($this->json = json_decode($html, true)) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed html with error context if exists
     */
    private function error_handler() {
        if (!empty($this->errors)) {
            echo '<ul class="wolerror">';
            foreach ($this->errors as $err) {
                echo '<li>' . $err . '</li>';
            }
            echo '</ul>';
            return true;
        }
        return false;
    }

    /**
     * output debug data
     *
     * @param  mixed  string, array or object
     * @return echo   array or object as html output
     * @example self::debug($this->sinfo);
     */
    public function debug($str) {
        echo '<pre>' . (is_array($str) ? print_r($str, true) : $str) . '</pre>';
    }

}

/**
 * World of Logs Parser Storage File Class
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2010 - 2011 Branko Wilhelm
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @package    wol_parser
 * @version    $Id: class.wol.php 2 2011-09-20 22:32:09Z bRunO $
 */
final class WoLStorageFile {

    /**
     * @var string cachename
     */
    public $key = null;

    /**
     * @var string cache path
     */
    public $path = null;

    /**
     * @var string cachefile extension
     */
    public $ext = '.php';

    /**
     * @var integer timeout to re-create
     */
    public $timeout = 60;

    /**
     * store given data serialized into a file
     *
     * @param  string  filename
     * @param  array   data array
     * @return bool    false if an error occurred otherwise true
     */
    public function set($data) {
        if ($this->path === null) {
            trigger_error('WoL: no cache path given', E_USER_WARNING);
            return false;
        }

        if (!file_exists($this->path) || !is_writable($this->path)) {
            trigger_error("WoL: cache directory '" . $this->path . "' does't exists or isn't writable", E_USER_WARNING);
            return false;
        }

        return file_put_contents($this->path . $this->key . $this->ext, serialize($data));
    }

    /**
     * get unserialized data from cachefile
     *
     * @param  string filename
     * @return mixed  false or array
     */
    public function get() {

        if (!file_exists($this->path . $this->key . $this->ext)) {
            return false;
        }
        if (filemtime($this->path . $this->key . $this->ext) < time() - $this->timeout) {
            return false;
        }

        return unserialize(file_get_contents($this->path . $this->key . $this->ext));
    }

}

/**
 * World of Logs Parser Storage Memcache Class
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2010 - 2011 Branko Wilhelm
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @package    wol_parser
 * @version    $Id: class.wol.php 2 2011-09-20 22:32:09Z bRunO $
 */
final class WoLStorageMemcache {

    /**
     * @var resource kann overwrite with own memcache obj if exists
     */
    public $_db = null;

    /**
     * @var string cachename
     */
    public $key = null;

    /**
     * @var string memcache host
     */
    public $host = 'localhost';

    /**
     * @var integer memcache port
     */
    public $port = 11211;

    /**
     * @var integer timeout to re-create
     */
    public $timeout = 60;

    /**
     * connect the memcache server
     *
     * @return bool
     */
    private function connect() {
        $this->_db = new memcache;
        return $this->_db->connect($this->host, $this->port);
    }

    /**
     * store given data into memcache
     *
     * @param  string filename
     * @param  array  data array
     * @return bool   false if an error occurred otherwise true
     */
    public function set($data) {
        if ($this->_db == null) {
            $this->connect();
        }

        return $this->_db->set($this->key, $data, MEMCACHE_COMPRESSED, $this->timeout);
    }

    /**
     * get data from memcache
     *
     * @return mixed false or array
     */
    public function get() {
        if ($this->_db == null) {
            $this->connect();
        }
        return $this->_db->get($this->key);
    }

}

/**
 * World of Logs Parser Storage APC Class
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2010 - 2011 Branko Wilhelm
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @package    wol_parser
 * @version    $Id: class.wol.php 2 2011-09-20 22:32:09Z bRunO $
 */
final class WoLStorageAPC {

    /**
     * @var string cachename
     */
    public $key = null;

    /**
     * @var integer timeout to re-create
     */
    public $timeout = 60;

    /**
     * store given data into apc
     *
     * @param  string filename
     * @param  array  data array
     * @return bool   false if an error occurred otherwise true
     */
    public function set($data) {
        return apc_store($this->key, $data, $this->timeout);
    }

    /**
     * get data from apc
     *
     * @return mixed false or array
     */
    public function get() {
        return apc_fetch($this->key);
    }

}

/**
 * World of Logs Parser Storage XCache Class
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  2010 - 2011 Branko Wilhelm
 * @license    GNU Public License <http://www.gnu.org/licenses/gpl.html>
 * @package    wol_parser
 * @version    $Id: class.wol.php 2 2011-09-20 22:32:09Z bRunO $
 */
final class WoLStorageXCache {

    /**
     * @var string cachename
     */
    public $key = null;

    /**
     * @var integer timeout to re-create
     */
    public $timeout = 60;

    /**
     * store given data into xcache
     *
     * @param  string filename
     * @param  array  data array
     * @return bool   false if an error occurred otherwise true
     */
    public function set($data) {
        return xcache_set($this->key, $data, $this->timeout);
    }

    /**
     * get data from xcache
     *
     * @return mixed false or array
     */
    public function get() {
        if (!xcache_isset($this->key)) {
            return false;
        }

        return xcache_get($this->key);
    }

}
