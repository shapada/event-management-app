<?php

namespace EventManager\Refinement;

abstract class AbstractRefinement {

	/**
	 * Instance of a facets object.
	 *
	 * @var object
	 */
	public $facets;

	/**
	 * Instance of a data table object.
	 *
	 * @var object
	 */
	public $data_table;

	/**
	 * The data source ID for filtering the table.
	 *
	 * @var string
	 */
	protected $data_source = '';

	abstract protected function set_facets();
	abstract protected function set_data_table();

	public function __construct() {
		$this->set_facets();
		$this->set_data_table();
		$this->set_refinement_object();
	}

	public function register() {
		$this->data_table->register();

		if ( is_object( $this->facets ) ) {
			$this->facets->register();
		}

		add_filter( "event_manager_refine_results_for_{$this->data_source}",
			array( $this->data_table, 'refine_results' ), 10, 2 );
	}

	public function render_facets() {
		$this->facets->output_refinement_facets();
	}

	public function render_data_table() {
		$this->data_table->render();
	}

	/**
	 * Set the global refinement object. This controls whether facets need to be rendered in the primary nav.
	 */
	public function set_refinement_object() {
		event_manager_core()->refinement->set_refinement_object( $this );
	}

}
