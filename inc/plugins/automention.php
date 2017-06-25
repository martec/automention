<?php
/**
 * Automention
 * https://github.com/martec
 *
 * Copyright (C) 2015-2016, Martec
 *
 * Autocomplete Poll is licensed under the GPL Version 3, 29 June 2007 license:
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

define('AM_PLUGIN_VER', '1.3.5');

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

function automention_activate()
{
	global $db;

	require_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets("footer", '/$/', "{\$automention}");
}

function automention_deactivate()
{
	global $db;
	include_once MYBB_ROOT."inc/adminfunctions_templates.php";

	find_replace_templatesets("footer", '#'.preg_quote('{$automention}').'#', '',0);
}

$plugins->add_hook('global_start', 'automention');

function automention() {
	global $automention, $mybb, $cache;

	$plugin_local = array('calendar.php', 'editpost.php', 'modcp.php', 'newreply.php', 'newthread.php', 'showthread.php', 'private.php', 'usercp.php', 'warnings.php');
	$plu_dv = $cache->read("plugins");
	if ($plu_dv['active']['dvz_shoutbox']) {
		$plugin_local[] = "index.php";
	}
	foreach ($plugin_local as &$local) {
		if (THIS_SCRIPT == ''.$local.'') {
			$automention = "<script type=\"text/javascript\">var maxnamelength = '".$mybb->settings['maxnamelength']."'</script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/xregexp-all-min.js?ver=".AM_PLUGIN_VER."\"></script>
<link rel=\"stylesheet\" href=\"".$mybb->asset_url."/jscripts/automention/jquery.atwho.min.css?ver=".AM_PLUGIN_VER."\" type=\"text/css\" media=\"all\" />
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/jquery.caret.min.js?ver=".AM_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/jquery.atwho.min.js?ver=".AM_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/automention.js?ver=".AM_PLUGIN_VER."\"></script>";
		}
	}
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
		if(my_strlen($mybb->input['query']) < 2)
		{
			exit;
		}
		if($mybb->get_input('getone', MyBB::INPUT_INT) == 1)
		{
			$limit = 1;
		}
		else
		{
			$limit = 15;
		}
		// Send our headers.
		header("Content-type: application/json; charset={$charset}");
		// Query for any matching users.
		$query_options = array(
			"order_by" => "username",
			"order_dir" => "asc",
			"limit_start" => 0,
			"limit" => $limit
		);

		$query = $db->simple_select("users", "uid, username, avatar", "username LIKE '".$db->escape_string_like($mybb->input['query'])."%'", $query_options);
		if($limit == 1)
		{
			$user = $db->fetch_array($query);
			if(!$user['avatar']) {$user['avatar'] = defaultavatar();};
			$data = array('id' => $user['username'], 'text' => $user['username'], 'avatar' => $user['avatar']);
		}
		else
		{
			$data = array();
			while($user = $db->fetch_array($query))
			{
				if(!$user['avatar']) {$user['avatar'] = defaultavatar();};
				$data[] = array('id' => $user['username'], 'text' => $user['username'], 'avatar' => $user['avatar']);
			}
		}

		echo json_encode($data);
		exit;
	}
}
?>