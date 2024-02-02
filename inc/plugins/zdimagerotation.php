<?php
/**
 * Image rotation for random Banner images from given folder
 * Copyright 2024 ZnapShot
 */
 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}

$plugins->add_hook('global_intermediate', 'fetchImageRandomly');
$plugins->add_hook('global_end', 'fetchImageRandomly');

function zdimagerotation_info()
{
	return array(
		"name"			=> "ZD - Image Random Image Rotation",
		"description"	=> "Selects a random image from a given folder to be returned, useful for logos",
		"website"		=> "https://znapdev.de",
		"author"		=> "ZnapShot",
		"authorsite"	=> "https://znapdev.de",
		"version"		=> "1.0",
		"guid" 			=> "",
		"codename"		=> "zdimagerotation",
		"compatibility" => "18*"
	);
}

function zdimagerotation_install()
{
	global $db, $mybb;

	$setting_group = array(
			'name' => 'zdimagerotation',
			'title' => 'ZD Image Rotation Settings',
			'description' => 'Change behaviour of image rotation',
			'disporder' => 5, // The order your setting group will display
			'isdefault' => 0
	);

	$gid = $db->insert_query("settinggroups", $setting_group);

	$setting_array = array(
    'zdir_use_for_logo' => array(
        'title' => 'Use as Banner rotation?',
        'description' => 'Use randomly chosen images for Board Logo/Banner:',
        'optionscode' => 'yesno',
				'value'       => '1',
        'disporder' => 1),
		'zdir_image_dir' => array(
        'title' => 'Image Directory',
        'description' => 'Enter the folder where images are located:',
        'optionscode' => 'text',
        'value' => 'uploads/rotation/', // Default
        'disporder' => 2
    ),
	);

	foreach($setting_array as $name => $setting)
	{
			$setting['name'] = $name;
			$setting['gid'] = $gid;

			$db->insert_query('settings', $setting);
	}
	
	// Don't forget this!
	rebuild_settings();
}

function zdimagerotation_is_installed()
{
	global $mybb;
	if(isset($mybb->settings['zdir_image_dir']) || isset($mybb->settings['zdir_use_for_logo']))
	{
			return true;
	}

	return false;
}

function zdimagerotation_uninstall()
{
	global $db;

	$db->delete_query('settings', "name IN ('zdir_image_dir','zdir_use_for_logo')");
	$db->delete_query('settinggroups', "name = 'zdimagerotation'");

	rebuild_settings();
}

function zdimagerotation_activate(){}

function zdimagerotation_deactivate(){}

function fetchImageRandomly() {
	global $theme, $mybb;
	
	if(!str_starts_with($mybb->settings['zdir_image_dir'],"/")) $mybb->settings['zdir_image_dir'] = "/" . $mybb->settings['zdir_image_dir'];
	if(!str_ends_with($mybb->settings['zdir_image_dir'],"/")) $mybb->settings['zdir_image_dir'] = $mybb->settings['zdir_image_dir'] . "/";
	
	$dirPath = MYBB_ROOT . $mybb->settings['zdir_image_dir'];
	$files = scandir($dirPath);
	// do nothing if dir is empty
	if(count($files)==2) return;

	//randomize files
	$rand=0;
	while(!is_file(MYBB_ROOT . $mybb->settings['zdir_image_dir'] . $files[$rand]))
		$rand = rand(2,count($files)-1);

	global $zdRandImg;
	$zdRandImg = $mybb->settings['bburl'] . $mybb->settings['zdir_image_dir'] . $files[$rand];
	
	if($mybb->settings['zdir_use_for_logo']){
		$theme['logo']=$zdRandImg;
	}
}
