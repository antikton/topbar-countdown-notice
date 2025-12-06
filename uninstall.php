<?php
/**
 * Uninstall script for Topbar Countdown Notice
 *
 * This file is executed when the plugin is deleted from WordPress.
 * It removes all plugin data from the database.
 */

// Security: Exit if uninstall not called from WordPress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'tcn_settings' );

// For multisite installations
delete_site_option( 'tcn_settings' );
