<?php
/**
 * Autocomplete Poll
 * https://github.com/martec
 *
 * Copyright (C) 2015-2015, Martec
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

define('AM_PLUGIN_VER', '1.0.0');

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

$plugins->add_hook('global_start', 'autcompletepoll');

function autcompletepoll() {
	global $automention, $mybb;

	$plugin_local = array('calendar.php', 'editpost.php', 'modcp.php', 'newreply.php', 'newthread.php', 'showthread.php', 'private.php', 'usercp.php', 'warnings.php');
	foreach ($plugin_local as &$local) {
		if (THIS_SCRIPT == ''.$local.'') {
			$automention = "<script type=\"text/javascript\">var maxnamelength = '".$mybb->settings['maxnamelength']."'</script>
<link rel=\"stylesheet\" href=\"".$mybb->asset_url."/jscripts/automention/jquery.atwho.min.css?ver=".AM_PLUGIN_VER."\" type=\"text/css\" media=\"all\" />
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/jquery.caret.min.js?ver=".AM_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/jquery.atwho.min.js?ver=".AM_PLUGIN_VER."\"></script>
<script type=\"text/javascript\" src=\"".$mybb->asset_url."/jscripts/automention/automention.js?ver=".AM_PLUGIN_VER."\"></script>";
		}
	}
}
?>