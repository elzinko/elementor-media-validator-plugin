<?php

/**
* @package EMVP
*/

namespace EMVP\Base;

if (!defined('ABSPATH')) {
	die;
}

class Activate {

	public static function activate(){
		global $wpdb;
		flush_rewrite_rules();
		Activate::installTables();
	}

	public static function installTables() {
		global $wpdb;
		$media_actions = $wpdb->prefix . 'emvp_media_actions';
		$action_logs = $wpdb->prefix . 'emvp_action_logs';

		Activate::create_table_media_actions();
		Activate::create_table_action_logs();

		$version = "1.0.0";
		update_option( 'emvp_database_version', $version );
	}


	public static function create_table_media_actions() {
		global $wpdb;
	
		$table_name = $wpdb->prefix . 'emvp_media_actions';
	
		// Charset
		$charset_collate = $wpdb->get_charset_collate();
	
		// SQL for creating table
		$sql = "CREATE TABLE $table_name (
			action_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			media_id bigint(20) UNSIGNED NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			action_type varchar(20) NOT NULL,
			action_comment text,
			action_date datetime NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (action_id)
		) $charset_collate;";
	
		// Including the upgrade library for creating/updating tables
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
		// Creating the table
		dbDelta($sql);
	}
	
	public static function create_table_action_logs() {
		global $wpdb;
	
		$action_logs_table_name = $wpdb->prefix . 'emvp_action_logs';
		$media_actions_table_name = $wpdb->prefix . 'emvp_media_actions';
	
		// Charset
		$charset_collate = $wpdb->get_charset_collate();
	
		// SQL for creating table without foreign key
		$sql = "CREATE TABLE $action_logs_table_name (
			log_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			action_id bigint(20) UNSIGNED NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			action_type varchar(20) NOT NULL,
			action_date datetime NOT NULL DEFAULT current_timestamp(),
			PRIMARY KEY (log_id)
		) $charset_collate;";
	
		// Including the upgrade library for creating/updating tables
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
		// Creating the table
		dbDelta($sql);
	
		// Check if the table was created
		if ($wpdb->get_var("SHOW TABLES LIKE '$action_logs_table_name'") === $action_logs_table_name) {
			// SQL to add foreign key
			$alter_sql = "ALTER TABLE $action_logs_table_name ADD FOREIGN KEY (action_id) REFERENCES $media_actions_table_name(action_id) ON DELETE CASCADE;";
			// Execute the ALTER TABLE query to add the foreign key
			$wpdb->query($alter_sql);
		}
	}	
	
}