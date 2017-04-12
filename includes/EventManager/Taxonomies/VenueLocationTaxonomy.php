<?php

namespace EventManager\Taxonomies;

use EventManager\Taxonomies\BaseTaxonomy;

class VenueLocationTaxonomy extends BaseTaxonomy {

	/**
	 * The taxonomy name
	 * @var string The taxonomy name
	 */
	public $name = 'venue_location';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $post_types Supported post types.
	 */
	public function __construct() {}

	/**
	 * Register hooks and actions
	 */
	public function register() {
		parent::register();

		add_filter(
			"event_manager_filter_{$this->get_name()}_taxonomy_options",
			array( $this, 'taxonomy_options' )
		);
	}

	/**
	 * Returns internal taxonomy name.
	 *
	 * @access public
	 * @return string The taxonomy name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Returns the post types that will use the taxonomy.
	 *
	 * @access public
	 * @return string The taxonomy name.
	 */
	public function get_post_types() {
		return event_manager_core()->venue->get_cpts();
	}

	/**
	 * Get the singular taxonomy label.
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return 'Venue Location';
	}

	/**
	 * Get the plural taxonomy label.
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return 'Venue Locations';
	}

	/**
	 * Set the taxonomy options.
	 *
	 * @param array $options 	Current array of options.
	 * @return array 			Updated array of options.
	 */
	public function taxonomy_options( $options ) {
		$options['show_admin_column'] = true;
		return $options;
	}

}
