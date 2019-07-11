<?php
 /*
 * Plugin Name: Astraea
 * Plugin URI: https://www.espeaky.com
 * Description: A wordpress plugin that adds the ability to review users by user id.
 * Author: Scott Johnston
 * Author URI: https://www.linkedin.com/in/scott8johnston/
 * Version: 1.0.0
 * License: GPLv2 or later
 */

 /**
  * @author Scott Johnston
  * @license https://www.gnu.org/licenses/gpl-3.0.html
  * @package Astraea
  * @version 1.0.0
 */

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

class Astraea {	

	public function __construct(){		
		register_activation_hook(__FILE__, array($this,'plugin_activate')); 
		register_deactivation_hook(__FILE__, array($this,'plugin_deactivate')); 
	}		

	public function plugin_activate(){
		flush_rewrite_rules();	
		Astraea::create_table();		
	}

	public function plugin_deactivate(){
		flush_rewrite_rules();
		//Astraea::delete_table();	
	}

	private static function create_table(){		
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
	
		//Create table
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$create = "CREATE TABLE IF NOT EXISTS ".$wpdb->base_prefix."reviews (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,	
			text varchar(255) NOT NULL,		
			type varchar(10) NOT NULL,		
			senderId bigint NOT NULL,	
			receiverId bigint NOT NULL,	
			score tinyint NOT NULL,	
			ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		  ) ".$charset_collate.";";
		dbDelta($create);	
	}	
	
	private static function delete_table(){	
		/*global $wpdb;		
		$delete = "DROP TABLE IF EXISTS ".$wpdb->base_prefix."reviews;";
		$wpdb->query($delete );*/		
	}
}
include(plugin_dir_path(__FILE__) . 'astraea-admin.php');

include(plugin_dir_path(__FILE__) . 'astraea-api.php');

include(plugin_dir_path(__FILE__) . 'astraea-shortcode.php');

include(plugin_dir_path(__FILE__) . 'astraea-widget.php');

$astraea = new Astraea;
?>