<?php
/***************************************************************************
 *
 *   NewPoints Buy Usertitle and Username plugin (/inc/plugins/newpoints/newpoints_buynametitle.php)
 *	 Author: Pirata Nervo
 *   Copyright: © 2009-2011 Pirata Nervo
 *   
 *   Website: http://www.mybb-plugins.com
 *
 *   Allows users to buy a new username and/or usertitle.
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if($current_page == 'newpoints.php' && $GLOBALS['mybb']->action == 'buynametitle')
{
    global $templatelist;
    if(isset($templatelist))
    {
        $templatelist .= ',';
    }
    $templatelist .= 'newpoints_buynametitle,newpoints_buynametitle_buyname,newpoints_buynametitle_buytitle';
}
elseif($current_page == 'newpoints.php' && $GLOBALS['mybb']->action == 'stats')
{
    global $templatelist;
    if(isset($templatelist))
    {
        $templatelist .= ',';
    }
    $templatelist .= 'newpoints_buynametitle_stats,newpoints_buynametitle_stats_change,newpoints_buynametitle_stats_nochanges';
}

if (defined("IN_ADMINCP"))
{
	$plugins->add_hook("newpoints_admin_stats_noaction_end", "newpoints_buynametitle_admin_stats");
}
else
{
	// show plugin in the menu
	$plugins->add_hook("newpoints_default_menu", "newpoints_buynametitle_menu");
	
	// page
	$plugins->add_hook("newpoints_start", "newpoints_buynametitle_page");
	
	// stats
	$plugins->add_hook("newpoints_stats_start", "newpoints_buynametitle_stats");
}

function newpoints_buynametitle_info()
{
	return array(
		"name"			=> "Buy Usertitle and Username",
		"description"	=> "Allows users to buy a new username and/or usertitle.",
		"website"		=> "http://www.mybb-plugins.com",
		"author"		=> "Pirata Nervo",
		"authorsite"	=> "http://www.consoleaddicted.com",
		"version"		=> "1.2",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

function newpoints_buynametitle_install()
{
	global $db;
	
	// add settings
	newpoints_add_setting('newpoints_buynametitle_groups_title', 'newpoints_buynametitle', 'Groups allowed to change usertitle', 'Enter the group ID\'s, separated by a comma, of the user groups that can change user title. (leave blank if you want to allow all groups)', 'text', '', 1);
	newpoints_add_setting('newpoints_buynametitle_groups_name', 'newpoints_buynametitle', 'Groups allowed to change username', 'Enter the group ID\'s, separated by a comma, of the user groups that can change user name. (leave blank if you want to allow all groups)', 'text', '', 2);
	newpoints_add_setting('newpoints_buynametitle_title_fee', 'newpoints_buynametitle', 'Usertitle Change Price', 'Amount of money users need to pay to access the forums.', 'text', '100', 3);
	newpoints_add_setting('newpoints_buynametitle_name_fee', 'newpoints_buynametitle', 'Username Change Price', 'Amount of money users need to pay to access the forums.', 'text', '100', 4);
	newpoints_add_setting('newpoints_buynametitle_lastchanges', 'newpoints_buynametitle', 'Last Changes', 'Number of last changes to show in statistics.', 'text', '5', 5);
	
	rebuild_settings();
}

function newpoints_buynametitle_is_installed()
{
	global $db;
	
	$query = $db->simple_select('newpoints_settings', 'sid', 'plugin=\'newpoints_buynametitle\'', array('limit' => 1));
	if ($db->fetch_field($query, 'sid')) return true;
	
	return false;
}

function newpoints_buynametitle_uninstall()
{
	global $db;

	// delete settings
	newpoints_remove_settings("'newpoints_buynametitle_groups_title','newpoints_buynametitle_groups_name','newpoints_buynametitle_title_fee','newpoints_buynametitle_name_fee','newpoints_buynametitle_lastchanges'");
	rebuild_settings();
	
	newpoints_remove_log(array('buynametitle_name'));
	newpoints_remove_log(array('buynametitle_title'));
}

function newpoints_buynametitle_activate()
{
	global $db, $mybb;
	
	newpoints_add_template('newpoints_buynametitle', '
<html>
<head>
<title>{$lang->newpoints} - {$lang->newpoints_buynametitle}</title>
{$headerinclude}
</head>
<body>
{$header}
<table width="100%" border="0" align="center">
<tr>
<td valign="top" width="180">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>{$lang->newpoints_menu}</strong></td>
</tr>
{$options}
</table>
</td>
{$inline_errors}
<td valign="top">
{$buyname}
{$buytitle}
</td>
</tr>
</table>
{$footer}
</body>
</html>');

newpoints_add_template('newpoints_buynametitle_buyname', '
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="5"><strong>{$lang->newpoints_buynametitle_buy_username}</strong></td>
</tr>
<tr>
<td class="trow1" width="40%">{$lang->newpoints_buynametitle_name_description}</td>
<td class="trow1" width="20%" align="center">{$mybb->settings[\'newpoints_buynametitle_name_fee\']}</td>
<td class="trow1" width="40%" align="center"><form action="newpoints.php" method="POST"><input type="hidden" name="action" value="buynametitle_name" /><input type="hidden" name="postcode" value="{$mybb->post_code}" /><input type="text" class="textbox" name="username" /> <input type="submit" name="submit" value="{$lang->newpoints_buynametitle_submit}" /></form></td>
</tr>
</table>');

newpoints_add_template('newpoints_buynametitle_buytitle', '
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="5"><strong>{$lang->newpoints_buynametitle_buy_usertitle}</strong></td>
</tr>
<tr>
<td class="trow1" width="40%">{$lang->newpoints_buynametitle_title_description}</td>
<td class="trow1" width="20%" align="center">{$mybb->settings[\'newpoints_buynametitle_title_fee\']}</td>
<td class="trow1" width="40%" align="center"><form action="newpoints.php" method="POST"><input type="hidden" name="action" value="buynametitle_title" /><input type="hidden" name="postcode" value="{$mybb->post_code}" /><input type="text" class="textbox" name="usertitle" /> <input type="submit" name="submit" value="{$lang->newpoints_buynametitle_submit}" /></form></td>
</tr>
</table>');

	newpoints_add_template('newpoints_buynametitle_stats', '
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="4"><strong>{$lang->newpoints_buynametitle_last_changes}</strong></td>
</tr>
<tr>
<td class="tcat" width="30%"><strong>{$lang->newpoints_buynametitle_new_nametitle}</strong></td>
<td class="tcat" width="30%"><strong>{$lang->newpoints_buynametitle_old_nametitle}</strong></td>
<td class="tcat" width="20%" align="center"><strong>{$lang->newpoints_buynametitle_type}</strong></td>
<td class="tcat" width="20%" align="center"><strong>{$lang->newpoints_buynametitle_date}</strong></td>
</tr>
{$lastchanges}
</table><br />');
	
	newpoints_add_template('newpoints_buynametitle_stats_change', '
<tr>
<td class="{$bgcolor}" width="30%">{$new}</td>
<td class="{$bgcolor}" width="30%">{$old}</td>
<td class="{$bgcolor}" width="20%" align="center">{$type}</td>
<td class="{$bgcolor}" width="20%" align="center">{$date}</td>
</tr>');
	
	newpoints_add_template('newpoints_buynametitle_stats_nochanges', '
<tr>
<td class="trow1" width="100%" colspan="4">{$lang->newpoints_buynametitle_no_changes}</td>
</tr>');

	// edit templates
	newpoints_find_replace_templatesets('newpoints_statistics', '#'.preg_quote('<td valign="top" width="40%">').'#', '<td valign="top" width="40%">{$newpoints_buynametitle}');
}

function newpoints_buynametitle_deactivate()
{
	global $db, $mybb;
	
	newpoints_remove_templates("'newpoints_buynametitle','newpoints_buynametitle_buyname','newpoints_buynametitle_buytitle','newpoints_buynametitle_stats','newpoints_buynametitle_stats_change','newpoints_buynametitle_stats_nochanges'");
	
	// remove edits
	newpoints_find_replace_templatesets('newpoints_statistics', '#'.preg_quote('{$newpoints_buynametitle}').'#', '');
}

// show plugin in the list
function newpoints_buynametitle_menu(&$menu)
{
	global $mybb, $lang;
	newpoints_lang_load("newpoints_buynametitle");
	
	if ($mybb->input['action'] == 'buynametitle')
		$menu[] = "&raquo; <a href=\"{$mybb->settings['bburl']}/newpoints.php?action=buynametitle\">".$lang->newpoints_buynametitle."</a>";
	else
		$menu[] = "<a href=\"{$mybb->settings['bburl']}/newpoints.php?action=buynametitle\">".$lang->newpoints_buynametitle."</a>";
}

/* 
 * Checks if the primary or any of the additional groups of the current user are in the groups list passed as a parameter
 * @param String group ids separated by a comma
 * @return Boolean true if the user has permissions, false if not
*/
function newpoints_buynametitle_check_permissions($groups_comma)
{
    global $mybb;
    
    if ($groups_comma == '') return false;
    
    $groups = explode(",", $groups_comma);
    $add_groups = explode(",", $mybb->user['additionalgroups']);
    
    if (!in_array($mybb->user['usergroup'], $groups)) { // primary user group not allowed
        // check additional groups
        if ($add_groups) {
            if (count(array_intersect($add_groups, $groups)) == 0)
                return false;
            else
                return true;
        }
        else 
            return false;
    }
    else
        return true;
}

function newpoints_buynametitle_page()
{
	global $mybb, $db, $lang, $cache, $theme, $header, $templates, $plugins, $headerinclude, $footer, $options, $inline_errors;
	
	if (!$mybb->user['uid'])
		return;
		
	if ($mybb->input['action'] != 'buynametitle' && $mybb->input['action'] != 'buynametitle_title' && $mybb->input['action'] != 'buynametitle_name') return;
		
	newpoints_lang_load("newpoints_buynametitle");
	
	$plugins->run_hooks("newpoints_buynametitle_start");
		
	if ($mybb->input['action'] == 'buynametitle_name' || $mybb->input['action'] == 'buynametitle_title')
	{
		verify_post_check($mybb->input['postcode']);
		
		$plugins->run_hooks("newpoints_buynametitle_subscribe");
		
		// do we have permissions to buy username?
		if (!newpoints_buynametitle_check_permissions($mybb->settings['newpoints_buynametitle_groups_name']) && $mybb->settings['newpoints_buynametitle_groups_name'] != '' && $mybb->input['action'] == 'buynametitle_name')
		{
			error_no_permission();
		}
		
		// do we have permissions to buy usertitle?
		if (!newpoints_buynametitle_check_permissions($mybb->settings['newpoints_buynametitle_groups_title']) && $mybb->settings['newpoints_buynametitle_groups_title'] != '' && $mybb->input['action'] == 'buynametitle_title')
		{
			error_no_permission();
		}
		
		if ($mybb->input['action'] == 'buynametitle_name')
		{
			$field = 'username';
			$field2 = 'name';
		}
		else
		{
			$field = 'usertitle';
			$field2 = 'title';
		}
		
		if (floatval($mybb->settings["newpoints_buynametitle_{$field2}_fee"]) > floatval($mybb->user['newpoints']))
		{
			error($lang->newpoints_buynametitle_not_enough);
		}
		
		// Set up user handler to validate and update the title and/or username
		require_once "inc/datahandlers/user.php";
		$userhandler = new UserDataHandler("update");
		
		//die(""."newpoints_buynametitle_{$field2}_fee"." | ".$mybb->settings["newpoints_buynametitle_{$field2}_fee"]);

		$user = array(
			"uid" => $mybb->user['uid'],
			$field => $mybb->input[$field]
		);

		$userhandler->set_data($user);
		
		$errors = array();

		if(!$userhandler->validate_user())
		{
			$errors = $userhandler->get_friendly_errors();
		}
		else
		{
			$userhandler->update_user();
			
			// get points from user
			newpoints_addpoints($mybb->user['uid'], -(floatval($mybb->settings["newpoints_buynametitle_{$field2}_fee"])));
			
			$plugins->run_hooks("newpoints_buynametitle_success");
			
			if ($mybb->input['action'] == 'buynametitle_name')
			{
				$langstring = $lang->newpoints_buynametitle_log_name;
			}
			else
			{
				$langstring = $lang->newpoints_buynametitle_log_title;
			}
			
			$log_message = $lang->sprintf($langstring, $mybb->user[$field], $userhandler->data[$field]);
			
			// log purchase
			newpoints_log('buynametitle_'.$field, $log_message);
			
			redirect($mybb->settings['bburl']."/newpoints.php?action=buynametitle", $lang->newpoints_buynametitle_updated, $lang->newpoints_buynametitle_updated_title);
		}
		
		if (count($errors) > 0)
		{
			$inline_errors = inline_error($errors);
			$mybb->input['action'] = 'buynametitle';
		}
	}

	// show the buy username and usertitle page

	// do we have permissions to buy username?
	if (newpoints_buynametitle_check_permissions($mybb->settings['newpoints_buynametitle_groups_name']) && $mybb->settings['newpoints_buynametitle_groups_name'] != '')
	{
		$mybb->settings['newpoints_buynametitle_name_fee'] = newpoints_format_points($mybb->settings['newpoints_buynametitle_name_fee']);
		eval("\$buyname = \"".$templates->get('newpoints_buynametitle_buyname')."\";");
	}
	elseif ($mybb->settings['newpoints_buynametitle_groups_name'] == '')
	{
		$mybb->settings['newpoints_buynametitle_name_fee'] = newpoints_format_points($mybb->settings['newpoints_buynametitle_name_fee']);
		eval("\$buyname = \"".$templates->get('newpoints_buynametitle_buyname')."\";");
	}
	
	if (!isset($buyname))
	{
		$buyname = '';
		$br = '';
	}
	else $br = '<br />';
	
	// do we have permissions to buy usertitle?
	if (newpoints_buynametitle_check_permissions($mybb->settings['newpoints_buynametitle_groups_title']) && $mybb->settings['newpoints_buynametitle_groups_title'] != '')
	{
		$mybb->settings['newpoints_buynametitle_title_fee'] = newpoints_format_points($mybb->settings['newpoints_buynametitle_title_fee']);
		eval("\$buytitle = \"".$br.$templates->get('newpoints_buynametitle_buytitle')."\";");
	}
	elseif ($mybb->settings['newpoints_buynametitle_groups_title'] == '')
	{
		$mybb->settings['newpoints_buynametitle_title_fee'] = newpoints_format_points($mybb->settings['newpoints_buynametitle_title_fee']);
		eval("\$buytitle = \"".$br.$templates->get('newpoints_buynametitle_buytitle')."\";");
	}
	
	if (empty($buyname) && empty($buytitle))
	{
		error_no_permission();
	}
	
	eval("\$page = \"".$templates->get('newpoints_buynametitle')."\";");
	
	$plugins->run_hooks("newpoints_buynametitle_end");
	
	// output page
	output_page($page);
}

function newpoints_buynametitle_stats()
{
	global $mybb, $db, $templates, $cache, $theme, $newpoints_buynametitle, $lastchanges, $lang;
	
	// load language
	newpoints_lang_load("newpoints_buynametitle");
	$lastchanges = '';
	
	// build stats table
	$query = $db->simple_select('newpoints_log', '*', 'action=\'buynametitle_usertitle\' OR action=\'buynametitle_username\'', array('order_by' => 'date', 'order_dir' => 'DESC', 'limit' => intval($mybb->settings['newpoints_buynametitle_lastchanges'])));
	while($log = $db->fetch_array($query)) {
		$bgcolor = alt_trow();
		$data = explode('-', $log['data']);
		
		$new = build_profile_link(htmlspecialchars_uni($data[1]), intval($log['uid']));
		$old = build_profile_link(htmlspecialchars_uni($data[0]), intval($log['uid']));
		
		$date = my_date($mybb->settings['dateformat'], intval($log['date']), '', false);
		
		$type = ($log['action'] == 'buynametitle_usertitle') ? 'Usertitle' : 'Username';
		
		eval("\$lastchanges .= \"".$templates->get('newpoints_buynametitle_stats_change')."\";");
	}
	
	if (!$lastchanges)
		eval("\$lastchanges = \"".$templates->get('newpoints_buynametitle_stats_nochanges')."\";");
		
	eval("\$newpoints_buynametitle = \"".$templates->get('newpoints_buynametitle_stats')."\";");
}

function newpoints_buynametitle_admin_stats()
{
	global $form, $db, $lang, $mybb;
	
	newpoints_lang_load("newpoints_buynametitle");
	
	echo "<br />";
	
	// table
	$table = new Table;
	$table->construct_header($lang->newpoints_buynametitle_new_nametitle, array('width' => '30%'));
	$table->construct_header($lang->newpoints_buynametitle_old_nametitle, array('width' => '30%'));
	$table->construct_header($lang->newpoints_buynametitle_type, array('width' => '20%', 'class' => 'align_center'));
	$table->construct_header($lang->newpoints_buynametitle_date, array('width' => '20%', 'class' => 'align_center'));

	// build stats table
	$query = $db->simple_select('newpoints_log', '*', 'action=\'buynametitle_usertitle\' OR action=\'buynametitle_username\'', array('order_by' => 'date', 'order_dir' => 'DESC', 'limit' => intval($mybb->settings['newpoints_buynametitle_lastchanges'])));
	while($log = $db->fetch_array($query)) {
		$bgcolor = alt_trow();
		$data = explode('-', $log['data']);
		
		$new = build_profile_link(htmlspecialchars_uni($data[1]), intval($log['uid']));
		$old = build_profile_link(htmlspecialchars_uni($data[0]), intval($log['uid']));
		
		$date = my_date($mybb->settings['dateformat'], intval($log['date']), '', false);
		$type = ($log['action'] == 'buynametitle_usertitle') ? 'Usertitle' : 'Username';
		
		$table->construct_cell($new);
		$table->construct_cell($old);
		$table->construct_cell($type, array('class' => 'align_center'));
		$table->construct_cell(my_date($mybb->settings['dateformat'], intval($log['date']), '', false).", ".my_date($mybb->settings['timeformat'], intval($log['date'])), array('class' => 'align_center'));
		
		$table->construct_row();
	}
	
	if($table->num_rows() == 0)
	{
		$table->construct_cell($lang->newpoints_buynametitle_no_changes, array('colspan' => 4));
		$table->construct_row();
	}
	
	$table->output($lang->newpoints_buynametitle_last_changes);
}

?>