<?php
namespace EventManager\Entertainers;

use EventManager\Refinement\AbstractRefinement;
use EventManager\DataTables\Column;

/**
 * Class to manage refinement for the education CPT.
 */
class EntertainersRefinement extends AbstractRefinement {

	protected $data_source = 'entertainers';

	public $facets;

	/**
	 * Set the facets.
	 */
	protected function set_facets() {
		$this->facets = new EntertainersFacet( $this->data_source );
	}

	/**
	 * Setup data table.
	 */
	protected function set_data_table() {
		$this->data_table = new EntertainersDataTable();

		$this->data_table->add_column( new Column( 'Name', 'title', true ) );
		$this->data_table->add_column( new Column( 'Type', 'type' ) );

		$this->data_table->set_facets( $this->facets );
	}
}
