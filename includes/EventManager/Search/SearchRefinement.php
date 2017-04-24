<?php
namespace EventManager\Search;

use EventManager\Refinement\AbstractRefinement;
use EventManager\DataTables\Column;

/**
 * Class to manage refinement for the education CPT.
 */
class SearchRefinement extends AbstractRefinement {

	protected $data_source = 'search';

	public $facets;

	/**
	 * Set the facets.
	 */
	protected function set_facets() {
		$this->facets = new SearchFacet( $this->data_source );
	}

	/**
	 * Setup data table.
	 */
	protected function set_data_table() {
		$this->data_table = new SearchDataTable();

		$this->data_table->add_column( new Column( 'Name', 'title', true ) );
		$this->data_table->add_column( new Column( 'Type', 'type' ) );

		$this->data_table->set_facets( $this->facets );
	}
}
