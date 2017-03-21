<?php

namespace EventManager;

use EventManager\Entertainer;
use EventManager\Venue;

/**
 * Core functionality for the Event Manager Application
 */
class Core {

	/**
	 * @var \EventManager\Entertainer
	 */
	public $entertainer;

	/**
	 * @var \EventManager\Venue
	 */
	public $venue;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$this->entertainer = new Entertainer();
		$this->venue = new Venue();

		do_action( 'event_manager_core_init' );
	}

	/**
	 * Register hooks and actions.
	 */
	public function register() {

		$this->entertainer->register();
		$this->venue->register();

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	/**
	 * Run actions that need to happen at init, and allow other plugins to hook into
	 * core init.
	 */
	public function init() {
		/**
		 * Allow adding plugins and themes to hook into core init.
		 */
		do_action( 'event_manager_core_init' );
	}

	/**
	 * Admin init action, handles anything that's needed on admin init.
	 */
	public function admin_init() {}

	/**
	 * Register and enqueue scripts.
	 */
	public function enqueue_scripts() {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script(
			'event-manager-core', 
			EVENT_MANAGER_CORE_ABSURL . "/assets/js/event-manager-core{$min}.js",
			array( 'event-manager-vendor-core', 'jquery' ),
			EVENT_MANAGER_CORE_VERSION,
			true
		);

		wp_enqueue_script(
			'event-manager-vendor-core', 
			EVENT_MANAGER_CORE_ABSURL . "/assets/js/event-manager-vendor-core{$min}.js",
			array( 'jquery' ),
			EVENT_MANAGER_CORE_VERSION,
			true
		);
	}

	/**
	 * Register and enqueue styles.
	 */

	public function enqueue_styles() {
		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style(
			'event-manager-vendor-core', 
			EVENT_MANAGER_CORE_ABSURL . "/assets/css/event-manager-vendor-core{$min}.css",
			array(),
			EVENT_MANAGER_CORE_VERSION
		);

		wp_enqueue_style(
			'event-manager-core', 
			EVENT_MANAGER_CORE_ABSURL . "/assets/css/event-manager-core{$min}.css",
			array(),
			EVENT_MANAGER_CORE_VERSION
		);

		wp_enqueue_style(
			'event-manager-fa',
			EVENT_MANAGER_CORE_ABSURL . "/assets/fonts/css/font-awesome.min.css"
		);
	}
}
