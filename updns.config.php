<?php
define('CPANEL_USER',"username");
define('CPANEL_PASSWORD', "password");
define('CPANEL_HOST', "your-panel-host.com");
define('CPANEL_PORT', "2082");
define('CPANEL_DOMAIN', "example.info");


/** 
 * Updates 
 *    example.info.
 *  *.example.info.
 * 
 * Add / modify as you need
 * 
 * DON'T FORGET ABOUT THE DOT AT THE END!
 */

$update_names=array(
	      CPANEL_DOMAIN.'.'
	,'*.'.CPANEL_DOMAIN.'.'
	);