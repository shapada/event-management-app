<?php
namespace EventManager\Helpers\Taxonomies;

/**
 * Loop through an array of term IDs and include any child terms they have.
 *
 * @param  array $term_ids Existing array of term IDs.
 * @param  string $taxonomy The taxonomy the terms belong to.
 * @return array
 */
function append_child_term_ids( $term_ids, $taxonomy ) {
	foreach ( (array) $term_ids as $term_id ) {
		$child_term_ids = get_term_children( absint( $term_id ), $taxonomy );

		if ( empty( $child_term_ids ) || is_wp_error( $child_term_ids ) ) {
			continue;
		}

		$term_ids = array_merge( $term_ids, (array) $child_term_ids );
	}

	return $term_ids;
}

/**
 * Determine if query args contain a "tax_query" item.
 *
 * @param  array $query_args Array of query args.
 * @return boolean
 */
function has_tax_query( $query_args ) {
	return isset( $query_args['tax_query'] );
}

/**
 * Determine if query args contain a "meta_query" item.
 *
 * @param  array $query_args Array of query args.
 * @return boolean
 */
function has_meta_query( $query_args ) {
	return isset( $query_args['meta_query'] );
}

/**
 * Unset the "tax_query" arg if it exists.
 *
 * @param  array $query_args Array of query args.
 * @return array
 */
function unset_tax_query( $query_args ) {
	if ( has_tax_query( $query_args ) ) {
		unset( $query_args['tax_query'] );
	}

	return $query_args;
}
