<?php
namespace EventManager\Entertainers;

use EventManager\Refinement\AbstractFacets;

/**
 * Class to manage facets for the education CPT.
 */
class EntertainersFacet extends AbstractFacets {

	public function __construct( $data_source, $passed_facets = array() ) {
		global $wp_query;
		$entertainers = $wp_query->get_queried_object();
		if ( $entertainers->post_type === event_manager_core()->entertainer->get_cpts() ) {
			$passed_facets[] = array(
				'title'  => 'Entertainers',
				'items'  => array(
					'type'  => 'custom',
					'slug'  => 'entertainers-id',
					'value' => absint( $entertainers->ID ),
				),
				'format' => 'hidden',
			);
		}
		parent::__construct( $data_source, $passed_facets );
	}
}
