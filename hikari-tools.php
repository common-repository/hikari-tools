<?php
/*
Plugin Name: Hikari Tools Framework
Plugin URI: https://Hikari.ws/tools/
Description: A plugin development framework with a lot of reusable code and a nice settings page builder
Version: 1.07.05
Author: Hikari
Author URI: http://Hikari.ws
*/

/**!
*
* I, Hikari, from http://Hikari.WS , and the original author of the Wordpress plugin named
* Hikari Tools, please keep this license terms and credit me if you redistribute the plugin
*
*
*
*   This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
/*****************************************************************************
* © Copyright Hikari (http://wordpress.Hikari.ws), 2010
* If you want to redistribute this script, please leave a link to
* http://hikari.WS
*
* Parts of this code are provided or based on ideas and/or code written by others
* Translations to different languages are provided by users of this script
* IMPORTANT CONTRIBUTIONS TO THIS SCRIPT (listed in alphabetical order):
*
* Hackadelic SEO Table Of Contents: http://hackadelic.com/solutions/wordpress/seo-table-of-contents
* Thematic theme: http://themeshaper.com/thematic/
*
* Please send a message to the address specified on the page of the script, for credits
*
* Other contributors' (nick)names may be provided in the header of (or inside) the functions
* SPECIAL THANKS to all contributors and translators of this script !
*****************************************************************************/
define('HkTools_basename',plugin_basename(__FILE__));
define('HkTools_pluginfile',__FILE__);



global $wp_version;
$HkToolsOK = true;

if(version_compare(PHP_VERSION, '5.2') < 0){
	add_action('admin_notices',create_function(null,
	' echo "
<div class=\"error\">
<p>The plugin <strong>Hikari Tools Framework</strong> requires at least PHP version <em>5.2</em>, but you\'re using <strong>".PHP_VERSION."</strong>, please update.</p>
</div>
";'
	),1);
	$HkToolsOK = false;
}
if(version_compare($wp_version, '2.8') < 0){
	add_action('admin_notices',create_function(null,
	' echo "
<div class=\"error\">
<p>The plugin <strong>Hikari Tools Framework</strong> requires at least Wordpress version <em>2.8</em>, but you\'re using <strong>".$wp_version."</strong>, please update.</p>
</div>
";'
	),1);
	$HkToolsOK = false;
}



if($HkToolsOK){
	define('HkTools_version',"1.07");

	require_once 'class.hikari-tools.php';
}



