<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class ModWorldOfLogsHelper extends WoWModuleAbstract
{
    private $zones = array(
        4812 => 'ICC',
        4987 => 'RS',
        4493 => 'OS',
        4273 => 'Ulduar',
        4722 => 'PDK',
        3456 => 'Nax',
        2159 => 'Ony',
        4603 => 'VA',
        4500 => 'Malygos',
        5600 => 'BH',
        5334 => 'BoT',
        5094 => 'BWD',
        5638 => 'T4W',
        5723 => 'FL',
        5892 => 'DS',
        6125 => 'Mogu',
        6297 => 'HoF',
        6067 => 'ToES',
        6622 => 'ToT',
        6738 => 'SoO',
        6996 => 'HM',
        6967 => 'BF'
    );

    protected function getInternalData()
    {
        try
        {
            $result = WoW::getInstance()->getAdapter('WorldOfLogs')->getData($this->params->module->get('guild'));
        } catch (Exception $e)
        {
            return $e->getMessage();
        }

        foreach ($result->body->rows as $key => $row)
        {
            $row->duration = $this->duration($row->duration);

            if (!empty($row->zones))
            {
                $row->name = isset($this->zones[$row->zones[0]->id]) ? $this->zones[$row->zones[0]->id] : $row->zones[0]->name;
                $row->limit = $row->zones[0]->playerLimit;
                $row->mode = $row->zones[0]->difficulty;
            } else
            {
                $row->name = 'Unknown';
                $row->lmit = 0;
                $row->mode = 0;
            }

            if ($key >= $this->params->module->get('raids'))
            {
                unset($result->body->rows[$key]);
                continue;
            }
        }

        return $result->body->rows;
    }

    private function duration($msec)
    {
        $hour = (int)($msec / 1000 / 60 / 60);
        $msec = $msec - $hour * 60 * 60 * 1000;
        $min = (int)($msec / 1000 / 60);
        $msec = $msec - $min * 60 * 1000;
        $sec = (int)($msec / 1000);

        return $hour . ':' . $min;
    }
}