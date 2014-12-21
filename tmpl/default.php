<?php

/**
 * @author     Branko Wilhelm <branko.wilhelm@gmail.com>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2015 Branko Wilhelm
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @var        array $logs
 * @var        stdClass $module
 * @var        Joomla\Registry\Registry $params
 */

defined('_JEXEC') or die;

JFactory::getDocument()->addStyleSheet('media/' . $module->module . '/css/default.css');
?>
<?php if ($params->get('ajax')) : ?>
    <div class="mod_world_of_logs ajax"></div>
<?php else: ?>
    <table class="mod_world_of_logs">
        <thead>
        <tr>
            <th class="raid"><strong><?php echo JText::_('MOD_WORLD_OF_LOGS_RAID'); ?></strong></th>
            <th class="duration"><strong><?php echo JText::_('MOD_WORLD_OF_LOGS_DURATION'); ?></strong></th>
            <th class="bossCount" title="bossCount">&nbsp;</th>
            <th class="killCount" title="killCount">&nbsp;</th>
            <th class="wipeCount" title="wipeCount">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td class="raid"><?php echo JHtml::_('date', $log->dateString, 'd.m') . ' ' . JHtml::_('link', 'http://www.worldoflogs.com/reports/' . $log->id, $log->name, array('target' => '_blank')); ?>
                    <span>(<?php echo $log->limit . $log->mode; ?>)</span></td>
                <td class="duration"><?php echo $log->duration; ?></td>
                <td class="bossCount"><?php echo $log->bossCount; ?></td>
                <td class="killCount"><?php echo $log->killCount; ?></td>
                <td class="wipeCount"><?php echo $log->wipeCount; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>