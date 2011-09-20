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
$base = JURI::base(true);
JFactory::getDocument()->addStyleSheet($base . '/modules/mod_world_of_logs/tmpl/stylesheet.css', 'text/css', 'all');
?>
<table class="mod_world_of_logs<?php echo $params->get('moduleclass_sfx'); ?>">
    <thead>
        <tr>
            <th><b>Raid</b></th>
            <th><b>Dur.</b></th>
            <th><img src="<?php echo $base; ?>/modules/mod_world_of_logs/tmpl/images/boss.png" width="16" height="16" alt="" title="bossCount" /></th>
            <th><img src="<?php echo $base; ?>/modules/mod_world_of_logs/tmpl/images/kills.png" width="16" height="16" alt="" title="killCount" /></th>
            <th><img src="<?php echo $base; ?>/modules/mod_world_of_logs/tmpl/images/wipes.png" width="16" height="16" alt="" title="wipeCount" /></th>
        </tr>
    </thead>
    <?php
    echo $wol->tbody(); // output only the tbody
    ?>
</table>