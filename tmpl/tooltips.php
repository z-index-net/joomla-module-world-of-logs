<?php
/**
 * World of Logs latest Raids Module
 *
 * @author     Branko Wilhelm <bw@z-index.net>
 * @link       http://www.z-index.net
 * @copyright  (c) 2011 - 2013 Branko Wilhelm
 * @package    mod_world_of_logs
 * @license    GNU General Public License v3
 * @version    $Id: default.php 21 2013-04-02 20:06:31Z bRunO $
 */

defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');

$base = JURI::base(true);
JFactory::getDocument()->addStyleSheet($base . '/modules/mod_world_of_logs/tmpl/stylesheet.css', 'text/css', 'all');
?>
<table class="mod_world_of_logs<?php echo $params->get('moduleclass_sfx'); ?>">
    <thead>
        <tr>
            <th><strong><?php echo JText::_('MOD_WOL_RAID'); ?></strong></th>
            <th><strong><?php echo JText::_('MOD_WOL_DURATION'); ?></strong></th>
            <th><img src="<?php echo $base; ?>/modules/mod_world_of_logs/tmpl/images/boss.png" width="16" height="16" alt="" title="bossCount" /></th>
            <th><img src="<?php echo $base; ?>/modules/mod_world_of_logs/tmpl/images/kills.png" width="16" height="16" alt="" title="killCount" /></th>
            <th><img src="<?php echo $base; ?>/modules/mod_world_of_logs/tmpl/images/wipes.png" width="16" height="16" alt="" title="wipeCount" /></th>
        </tr>
    </thead>
    <tbody>
    <?php
    foreach($logs as $log): ?>
    <tr class="hasTip" title="<?php echo JText::_('MOD_WOL_PARTICIPANTS'); ?>::<?php echo implode('&lt;br/&gt;', $log->participants); ?>">
    	<td class="raid"><?php echo JHtml::_('date', $log->dateString, 'd.m') . ' ' . JHTML::_('link', 'http://www.worldoflogs.com/reports/' . $log->id, $log->name, array('target' => '_blank')); ?> <span>(<?php echo $log->limit . $log->mode; ?>)</span></td>
    	<td class="duration"><?php echo $log->duration; ?></td>
    	<td class="bossCount"><?php echo $log->bossCount; ?></td>
    	<td class="killCount"><?php echo $log->killCount; ?></td>
    	<td class="wipeCount"><?php echo $log->wipeCount; ?></td>
    </tr>
	<?php endforeach; ?>
    </tbody>
</table>