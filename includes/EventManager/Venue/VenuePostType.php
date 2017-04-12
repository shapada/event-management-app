<?php

namespace EventManager\Venue;

use EventManager\PostTypes\AbstractVenuePostType;

class VenuePostType extends AbstractVenuePostType {


	public $data_source = 'venue';

	public $post_type_slug = 'venue';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Register hooks/actions.
	 */
	public function register() {
		parent::register();
		add_filter( "event_manager_filter_{$this->get_name()}_post_type_options", [ $this, 'post_type_options' ] );

	}
	/**
	 * Set the CPT name.
	 *
	 * @return string	The CPT name.
	 */
	public function get_name() {
		return $this->post_type_slug;
	}

	/**
	 * Get data source for this CPT.
	 *
	 * @return string	The data source name.
	 */
	public function get_data_source() {
		return $this->data_source;
	}
}