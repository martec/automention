<?php
/**
 * Automention
 * https://github.com/martec
 *
 * Copyright (C) 2015-2021, Martec
 *
 * Automention is licensed under the GPL Version 3, 29 June 2007 license:
 *	http://www.gnu.org/copyleft/gpl.html
 *
 * @fileoverview Automention - Autocomplete Mention
 * @author Martec
 * @requires jQuery and Mybb
 * @credits At.js (http://ichord.github.io/At.js/).
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

define('AM_PLUGIN_VER', '1.4.0');

function automention_info()
{
	return array(
		'name'			=> 'Automention',
		'description'	=> 'Autocomplete Mention',
		'website'		=> '',
		'author'		=> 'martec',
		'authorsite'	=> '',
		'version'		=> AM_PLUGIN_VER,
		'compatibility' => '18*'
	);

}

function automention_install()
{
	global $db, $lang, $mybb;

	$lang->load('config_automention');

	$query	= $db->simple_select("settinggroups", "COUNT(*) as counts");
	$dorder = $db->fetch_field($query, 'counts') + 1;

	$groupid = $db->insert_query('settinggroups', array(
		'name'		=> 'automention',
		'title'		=> 'Autocomplete Mention',
		'description'	=> $lang->automention_sett_desc,
		'disporder'	=> $dorder,
		'isdefault'	=> '0'
	));

	$dorder_set = 0;
	$new_setting[] = array(
		'name'		=> 'automention_on_off',
		'title'		=> $lang->automention_onoff_title,
		'description'	=> $lang->automention_onoff_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'automention_limit_items',
		'title'		=> $lang->automention_limitems_title,
		'description'	=> $lang->automention_limitems_desc,
		'optionscode'	=> 'numeric',
		'value'		=> '10',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'automention_max_length',
		'title'		=> $lang->automention_maxlength_title,
		'description'	=> $lang->automention_maxlength_desc,
		'optionscode'	=> 'numeric',
		'value'		=> '15',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'automention_avatar_support',
		'title'		=> $lang->automention_avatar_title,
		'description'	=> $lang->automention_avatar_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'automention_space_support',
		'title'		=> $lang->automention_space_title,
		'description'	=> $lang->automention_space_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'automention_fulltext_support',
		'title'		=> $lang->automention_fulltext_title,
		'description'	=> $lang->automention_fulltext_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$new_setting[] = array(
		'name'		=> 'automention_thread_participants',
		'title'		=> $lang->automention_threadpart_title,
		'description'	=> $lang->automention_threadpart_desc,
		'optionscode'	=> 'yesno',
		'value'		=> '0',
		'disporder'	=> ++$dorder_set,
		'gid'		=> $groupid
	);

	$db->insert_query_multiple("settings", $new_setting);
	rebuild_settings();
}

function automention_is_installed()
{
	global $db;

	$query = $db->simple_select("settinggroups", "COUNT(*) as counts", "name = 'automention'");
	$rows  = $db->fetch_field($query, 'counts');

	return ($rows > 0);
}

function automention_uninstall()
{
	global $db;

	$groupid = $db->fetch_field(
		$db->simple_select('settinggroups', 'gid', "name='automention'"),
		'gid'
	);

	$db->delete_query('settings', 'gid=' . $groupid);
	$db->delete_query("settinggroups", "name = 'automention'");
	rebuild_settings();
}

global $settings;

if($settings['automention_on_off']) {
	$plugins->add_hook('pre_output_page', 'automention');
}

function automention(&$aut_content) {
	global $mybb, $cache;

	$plugin_local = array('calendar.php', 'editpost.php', 'modcp.php', 'newreply.php', 'newthread.php', 'showthread.php', 'private.php', 'usercp.php', 'warnings.php');
	$plu_dv = $cache->read("plugins");
	if ($plu_dv['active']['dvz_shoutbox']) {
		$plugin_local[] = "index.php";
	}

	$automention = "<script type=\"text/javascript\">var aut_maxnamelength = '".$mybb->settings['maxnamelength']."',
	aut_maxnumberitems = '".$mybb->settings['automention_limit_items']."',
	aut_max_length = ".(int)$mybb->settings['automention_max_length'].",
	aut_spacesupp = '".$mybb->settings['automention_space_support']."',
	aut_avatar_set = '".$mybb->settings['automention_avatar_support']."',
	aut_thread_part = '".$mybb->settings['automention_thread_participants']."',
	aut_tid = ".$mybb->get_input('tid', MyBB::INPUT_INT).";
</script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/xregexp-all-min.js?ver=".AM_PLUGIN_VER."\"></script>
<link rel=\"stylesheet\" href=\"".$mybb->asset_url."/jscripts/automention/jquery.atwho.min.css?ver=".AM_PLUGIN_VER."\" type=\"text/css\" media=\"all\" />
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/jquery.caret.min.js?ver=".AM_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/jquery.atwho.min.js?ver=".AM_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/automention.js?ver=".AM_PLUGIN_VER."\"></script>";

	foreach ($plugin_local as &$local) {
		if (THIS_SCRIPT == ''.$local.'') {
			$aut_content = str_replace('</body>', $automention . '</body>', $aut_content);
		}
	}

	return $aut_content;
}

function defaultavatar() {

	global $mybb, $theme;


	// Default avatar
	if(defined('IN_ADMINCP'))
	{
		$theme['imgdir'] = '../images';
	}
	return str_replace('{theme}', $theme['imgdir'], $mybb->settings['useravatar']);
}

$plugins->add_hook('xmlhttp', 'am_get_users');
function am_get_users() {
	global $mybb, $db;

	if($mybb->input['action'] == "get_users_plus")
	{
		$mybb->input['query'] = ltrim($mybb->get_input('query'));
		// If the string is less than 2 characters, quit.
		if(my_strlen($mybb->input['query']) < 2 && !$mybb->settings['automention_thread_participants'])
		{
			exit;
		}
		if($mybb->get_input('getone', MyBB::INPUT_INT) == 1)
		{
			$limit = 1;
		}
		else
		{
			$limit = (int)$mybb->settings['automention_limit_items'];
		}
		// Send our headers.
		header("Content-type: application/json; charset={$charset}");
		// Query for any matching users.

		$users = $usernames = array();

		$tid = $mybb->get_input('tid', MyBB::INPUT_INT);
		if($tid && $mybb->settings['automention_thread_participants'])
		{
			$query = $db->query("SELECT DISTINCT u.uid, u.username, u.avatar FROM ".TABLE_PREFIX."users u INNER JOIN ".TABLE_PREFIX."posts p ON p.uid = u.uid INNER JOIN ".TABLE_PREFIX."threads t ON p.tid = t.tid WHERE t.tid = {$tid} ORDER BY username ASC LIMIT 0, {$limit}");
			while($user = $db->fetch_array($query))
			{
				$users[] = $user;
			}
		}
		else
		{
			$query_options = array(
				"order_by" => "username",
				"order_dir" => "asc",
				"limit_start" => 0,
				"limit" => $limit
			);

			$conds = '';
			$query = $db->simple_select("users", "uid, username, avatar", "username LIKE '".$db->escape_string_like($mybb->input['query'])."%'", $query_options);
			while($user = $db->fetch_array($query))
			{
				$users[] = $user;
				$usernames[] = $user['username'];
			}
			$limit = $limit - count($users);
			if($limit > 0 && $mybb->settings['automention_fulltext_support'])
			{
				if(count($users) > 0)
				{
					$conds = ' AND username NOT IN (\''.implode("', '", array_map(array($db, 'escape_string'), $usernames)).'\')';
				}
				$query_options['limit'] = $limit;
				$query = $db->simple_select("users", "uid, username, avatar", "username LIKE '%".$db->escape_string_like($mybb->input['query'])."%'".$conds, $query_options);
				while($user = $db->fetch_array($query))
				{
					$users[] = $user;
				}
			}
		}

		$data = array();
		foreach($users as $user)
		{
			if(!$user['avatar']) {$user['avatar'] = defaultavatar();};
			$data[] = array('id' => $user['username'], 'text' => $user['username'], 'avatar' => $user['avatar'], 'uid' => $user['uid']);
		}

		echo json_encode($data);
		exit;
	}
}
