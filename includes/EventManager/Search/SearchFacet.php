<?php
namespace EventManager\Search;

use EventManager\Refinement\AbstractFacets;

/**
 * Class to manage facets for the education CPT.
 */
class SearchFacet extends AbstractFacets {

	public function __construct( $data_source, $passed_facets = array() ) {
		$passed_facets[] = array(
			'title'  => 'Search',
			'items'  => array(
				'type'  => 'custom',
				'slug'  => 'search-item-id',
				'value' => 'search',
			),
			'format' => 'hidden',
		);


		parent::__construct( $data_source, $passed_facets );
	}
}
