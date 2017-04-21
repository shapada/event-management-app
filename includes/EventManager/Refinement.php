<?php

namespace EventManager;

/**
 * Base class for Refinement code.
 */
class Refinement {

	/**
	 * Refinement API module.
	 *
	 * @var \EventManager\Refinement\API
	 */
	public $api;

	/**
	 * The refinement object for the current page.
	 *
	 * @var object|null
	 */
	public $refinement_object = null;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->api = new Refinement\API();
	}

	public function register() {
		$this->api->register();
	}

	/**
	 * Determine if refinement filters should be shown for the current page.
	 *
	 * @return bool
	 */
	public function should_show_refinement_filters() {
		return ! empty( $this->refinement_object );
	}

	/**
	 * Set the refinement object for the current page.
	 *
	 * @param object $refinement_object
	 */
	public function set_refinement_object( $refinement_object ) {
		$this->refinement_object = $refinement_object;
	}

	/**
	 * Get the refinement object for the current page. This controls whether facets need to be rendered in the primary nav.
	 *
	 * @return object \EventManager\Refinement\AbstractRefinement
	 */
	public function get_refinement_object() {
		return $this->refinement_object;
	}
}
