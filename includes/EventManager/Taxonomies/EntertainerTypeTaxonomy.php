<?php

namespace EventManager\Taxonomies;

use EventManager\Taxonomies\BaseTaxonomy;

class EntertainerTypeTaxonomy extends BaseTaxonomy {

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
		return 'entertainer_type';
	}

	/**
	 * Returns the post types that will use the taxonomy.
	 *
	 * @access public
	 * @return string The taxonomy name.
	 */
	public function get_post_types() {
		return event_manager_core()->entertainer->get_cpts();
	}

	/**
	 * Get the singular taxonomy label.
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return 'Entertainer Type';
	}

	/**
	 * Get the plural taxonomy label.
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return 'Entertainer Types';
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
