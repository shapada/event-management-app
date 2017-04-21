<?php
namespace EventManager\Helpers;

/**
 * Wrapper function for PHP's error_log().
 *
 * @param  string $message The message to log
 * @param  string $tag A tag for a messsage so it's easier to search for in the log. This can be the calling method's name (__METHOD__) or some other identification.
 */
function log_message( $message, $tag = '' ) {
	if ( empty( $message ) ) {
		return;
	}

	$msg = $message;

	if ( ! empty( $tag ) ) {
		$msg = sprintf(
			'%s: %s',
			$tag,
			$msg
		);
	}

	error_log( $msg );
}

/**
 * Get a property from either an object or an array.
 *
 * @param  string $key The name of the property to retrieve
 * @param  array|object $data The object to retrieve the property for.
 * @return mixed
 */
function get_property( $key, $data ) {
	if ( is_array( $data ) ) {
		return get_array_property( $key, $data );
	} elseif ( is_object( $data ) ) {
		return get_object_property( $key, $data );
	}

	return null;
}

/**
 * Get a property from an array.
 *
 * @param  string $key The name of the property to retrieve
 * @param  array $data The array to retrieve the property for.
 * @return mixed
 */
function get_array_property( $key, $data ) {
	if ( ! isset( $data[ $key ] ) ) {
		return null;
	}

	return $data[ $key ];
}

/**
 * Get a property from an object.
 *
 * @param  string $key The name of the property to retrieve
 * @param  object $data The object to retrieve the property for.
 * @return mixed
 */
function get_object_property( $key, $data ) {
	if ( ! isset( $data->$key ) ) {
		return null;
	}

	return $data->$key;
}

/**
 * Determne if a post ID is valid. This function is needed because zero shouldn't count as a valid post ID. Getting a post using 0 as an ID will return the global post, which is not what we want in most cases.
 *
 * @param  int|string $post_id
 * @return boolean
 */
function is_valid_post_id( $post_id ) {
	return ( ! empty( $post_id ) && is_numeric( $post_id ) );
}

/**
 * Determne if a user ID is valid.
 *
 * @param  int|string $user_id
 * @return boolean
 */
function is_valid_user_id( $user_id ) {
	return ( ! empty( $user_id ) && is_numeric( $user_id ) );
}

/**
 * Get a post object, but only if the ID is not zero.
 *
 * @param  int|\WP_Post $post The post ID or object
 * @return \WP_Post|null
 */
function get_post( $post ) {
	if ( is_valid_post_id( $post )  ) {
		$post = \get_post( $post );
	}

	if ( ! is_a( $post, 'WP_Post' ) ) {
		return null;
	}

	return $post;
}

/**
 * Get the status for a post.
 *
 * @param  int|\WP_Post $post The post ID or object
 * @return string
 */
function get_post_status( $post ) {
	$post = \EventManager\Helpers\get_post( $post );

	if ( is_a( $post, 'WP_Post' ) ) {
		return $post->post_status;
	}

	return '';
}

/**
 * Retrieve the id for a post or user
 *
 * @param $object
 *
 * @return int id for a post or user; returns 0 if passed object is not an int, WP_Post or WP_User
 */
function normalize_post_or_user_id( $object ) {
	if ( is_object( $object ) && is_a( $object, 'WP_Post' ) || is_a( $object, 'WP_User' ) ) {
		$object = $object->ID;
	}

	return absint( $object );
}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child terms will be
 * placed under a 'children' member of their parent term.
 * @param Array   $terms     taxonomy term objects to sort
 * @param Array   $into     result array to put them in
 * @param integer $parentId the current parent ID to put them in
 */
function sort_terms_hierarchically( Array &$terms, Array &$into, $parentId = 0 ) {
	foreach ( $terms as $i => $term ) {
		if ( $term->parent == $parentId ) {
			$into[ $term->term_id ] = $term;
			unset( $terms[ $i ] );
		}
	}

	foreach ($into as $topCat) {
		$topCat->children = array();
		sort_terms_hierarchically( $terms, $topCat->children, $topCat->term_id );
	}
}

/**
 * Determine if the current environment is a local environment.
 *
 * @return bool
 */
function is_local() {
	// Just return the constant if it's set.
	if ( defined( 'LOCAL' ) ) {
		return LOCAL;
	}

	// Parse the url and determine if it's a local environment.
	$host = parse_url( home_url(), PHP_URL_HOST );
	return (bool) preg_match( '#\.dev$#i', $host );
}

/**
 * Send debug code to the Javascript console
 */
function debug_to_console( $data ) {
	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
		if ( is_array( $data ) || is_object( $data ) ) {
			echo( '<script>console.log("PHP: ' . esc_html( json_encode( $data ) ) . '");</script>' );
		} else {
			echo( '<script>console.log("PHP: ' . esc_html( $data ) . '");</script>' );
		}
	}
}

/**
 * Get a user object, but only if the ID is not zero.
 *
 * @param  int|\WP_User $user The user ID or object
 * @return \WP_User|null
 */
function get_user( $user ) {
	if ( is_valid_user_id( $user )  ) {
		$user = get_user_by( 'id', $user );
	}

	if ( ! is_a( $user, 'WP_User' ) ) {
		return null;
	}

	return $user;
}

/**
 * Get Admin Email Address
 *
 * @return mixed|void
 */
function get_admin_email() {
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		return get_site_option( 'admin_email' );
	} else {
		return get_option( 'admin_email' );
	}
}

/**
 * Get the search query in order of precedence.
 *
 * @return string
 */
function get_search_query() {
	$search_term = '';

	// Look for 'search_query' first; it should take precedence since it comes from the data table search boxes.
	$search_term = sanitize_text_field( $_GET['search_query'] );

	if ( empty( $search_term ) ) {
		$search_term = get_query_var( 'search' );
	}

	if ( empty( $search_term ) ) {
		$search_term = get_query_var( 's' );
	}

	return $search_term;
}
