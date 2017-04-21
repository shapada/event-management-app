<?php
/**
 * Plugin Name: Event Manager
 * Description: Core functionality for the Event Manager application.
 * Version:     1.0.0
 * Author:      Front Row Entertainment
 * Author URI:  http://front-row-entertainment.dev
 */

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

define( 'EVENT_MANAGER_CORE_VERSION', '1.0' );
define( 'EVENT_MANAGER_CORE_ABSURL',  plugins_url( '/', __FILE__ ) );
define( 'EVENT_MANAGER_CORE_ABSPATH', __DIR__ );
define( 'EVENT_MANAGER_CORE_INCLUDES', EVENT_MANAGER_CORE_ABSPATH . '/includes' );

/**
 * Get the global Core class.
 *
 * @return \EventManager\Core Global instance of the Core class.
 */
function event_manager_core() {
	global $event_manager_core;

	if ( empty( $event_manager_core ) ) {
		$event_manager_core = new EventManager\Core();
		$event_manager_core->register();
	}

	return $event_manager_core;
}

event_manager_core();
