<?php

namespace EventManager\Refinement;

/**
 * Refinement class.
 */
class API {

	/*
	* Base WP-API Endpoint
	*/
	public $base_endpoint = 'event-manager/v1/search/';

	/**
	 * Constructor.
	 */
	public function __construct() {

	}

	// Register any actions and hooks for this class.
	public function register() {
		add_action( 'wp_enqueue_scripts', 	array( $this, 'enqueue_scripts' ) );
		add_action( 'rest_api_init', 		array( $this, 'register_endpoints' ) );
	}

	/**
	 * Register API endpoints.
	 */
	public function register_endpoints() {
		register_rest_route( $this->base_endpoint,
			'refine', array(
				'methods' 				=> 'GET',
				'callback' 				=> array( $this, 'refine_search' ),
				'permission_callback' 	=> array( $this, 'valid_user_check' ),
				'args' 					=> array(
					'search_query' => array(
						'required' 			=> false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'taxonomies' => array(
						'required'			=> false,
					),
					'post_types' => array(
						'required'			=> false,
					),
					'custom_fields' => array(
						'required'			=> false,
					),
					'data_source' => array(
						'required' 			=> false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'paged' => array(
						'required' 			=> false,
						'sanitize_callback'	=> 'absint',
					)
				),
			) );
	}

	/**
	 * Handle the API request to refine a search query.
	 *
	 * @param \WP_REST_Request $request The API request.
	 */
	public function refine_search( \WP_REST_Request $request ) {
		$parameters = $request->get_params();

		// Get the data source parameter.
		$data_source = 'search';
		if ( isset( $parameters['data_source'] ) ) {
			$data_source = $parameters['data_source'];
		}

		// Attach the refinement handler needed to retreive results.
		do_action( "event_manager_attach_refinement_handler_for_{$data_source}", $data_source, $request );

		// Perform the search based on the passed parameters
		$results = $this->search_query( $parameters );

		if ( $results ) {
			wp_send_json_success( $results );
		} else {
			wp_send_json_error();
		}
	}

	/**
	 * Check that the user is logged in before processing the request.
	 *
	 * @param \WP_REST_Request $request 	The API request.
	 * @return boolean					True/False if the user is logged in.
	 */
	public function valid_user_check( \WP_REST_Request $request ) {
		return is_user_logged_in();
	}

	/**
	 * Localize script for JS endpoint.
	 */
	public function enqueue_scripts() {

		wp_localize_script( 'event-manager-core',
			'searchRefinementAPI',
			array(
				'root' 		=> esc_url_raw( rest_url( $this->base_endpoint . 'refine' ) ),
				'nonce' 	=> wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Perform search query based on passed parameters.
	 * @param $parameters - array of parameters that can be specified:
	 * - search_query: text for search query
	 * - taxonomies: (array) taxonomies and terms that are specified
	 * - post_types: (array) post_types that are specified
	 *
	 * @return string The rendered HTML for the query.
	 */
	public function search_query( $parameters ) {
		$data_source = 'search';
		$taxonomies = array();

		$parameters_taxonomy = json_decode( isset( $parameters['taxonomies'] ) ? $parameters['taxonomies'] : '', true );
		$parameters_post_types = json_decode( isset( $parameters['post_types'] ) ? $parameters['post_types'] : '', true );
		$parameters_custom_fields = json_decode( isset( $parameters['custom_fields'] ) ? $parameters['custom_fields'] : '', true );
		$query_args = array(
			'suppress_filters'       => false,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		);

		// Get the data source parameter.
		if ( isset( $parameters['data_source'] ) ) {
			$data_source = $parameters['data_source'];
		}

		// Check for taxonomies and add to query args
		if ( ! empty( $parameters_taxonomy ) ) {
			foreach ( $parameters_taxonomy as $taxonomy => $terms ) {
				if ( ! empty( $terms ) ) {
					$taxonomies[] = array(
						'taxonomy'  => $taxonomy,
						'field'     => 'term_id',
						'terms'     => (array) $terms,
					);
				}
			}
		}

		if ( ! empty( $taxonomies ) ) {
			$query_args['tax_query'] = $taxonomies;
			$query_args['tax_query']['relation'] = 'AND';
		}

		// Check for post_types and add to query args
		if ( isset( $parameters_post_types ) ) {
			$post_types = (array) $parameters_post_types;
		}

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				if ( ! empty( $post_type ) ) {
					$query_args['post_type'][] = $post_type[0];
				}
			}
		}

		// Check for custom fields
		if ( ! empty( $parameters_custom_fields ) ) {
			$custom_fields = array();

			foreach ( (array) $parameters_custom_fields as $field => $value ) {
				$field = sanitize_text_field( $field );

				// Sanitize the value and handle differently depending on if it's an array or not.
				if ( is_array( $value ) ) {
					if ( 1 < count( $value ) ) {
						$value = array_map( 'sanitize_text_field', $value );
					} else {
						$value = sanitize_text_field( $value[0] );
					}
				} else {
					$value = sanitize_text_field( $value );
				}

				if ( empty( $value ) || empty( $field ) ) {
					continue;
				}

				$custom_fields[ $field ] = $value;
			}

			$query_args = array_merge( $query_args, $custom_fields );
		}

		if ( ! empty( $parameters['page'] ) ) {
			$query_args['paged'] = absint( $parameters['page'] );
		}

		// Add search query to query args. Do this last because it should take precedence over the search query supplied other ways.
		if ( isset( $parameters['search_query'] ) ) {
			$query_args['s'] = $parameters['search_query'];
		}

		return apply_filters( 'event_manager_refine_results_for_' . $data_source, '', $query_args );
	}

}
