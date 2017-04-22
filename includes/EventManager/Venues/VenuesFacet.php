<?php
namespace EventManager\Venues;

use EventManager\Refinement\AbstractFacets;

/**
 * Class to manage facets for the education CPT.
 */
class VenuesFacet extends AbstractFacets {

	public function __construct( $data_source, $passed_facets = array() ) {
		global $wp_query;
		$venues = $wp_query->get_queried_object();
		if ( $venues->post_type === event_manager_core()->venue->get_cpts() ) {
			$passed_facets[] = array(
				'title'  => 'Venues',
				'items'  => array(
					'type'  => 'custom',
					'slug'  => 'venue-id',
					'value' => absint( $venues->ID ),
				),
				'format' => 'hidden',
			);
		}
		parent::__construct( $data_source, $passed_facets );
	}
}
