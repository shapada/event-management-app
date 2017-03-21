<?php
namespace EventManager\Utils;

/**
 * A utility class to assist getting and setting results from class method calls as transients.
 * Props to Darshan for the original code/idea this class is based on.
 */
class TransientCacher {

	/**
	 * The soruce class a cacher instance uses.
	 *
	 * @var mixed
	 */
	public $source;

	/**
	 * The lifespan in seconds for cached data. Don't set this to 0 because transients with no expiration date are autoloaded.
	 *
	 * @var integer
	 */
	public $cache_duration = DAY_IN_SECONDS;

	/**
	 * Override the cache for a single method call.
	 *
	 * @var boolean
	 */
	public $override_cache = false;

	function __construct( $source ) {
		$this->source = $source;
	}

	/**
	 * Catch method calls from the source class and handle them.
	 *
	 * @param  string $name The method name
	 * @param  array $arguments Array of parameters included.
	 * @return mixed
	 */
	function __call( $name, $arguments ) {
		$result = $this->call_source_and_cache(
			$name, $arguments
		);

		// Always set the override property back to false.
		$this->override_cache = false;

		return $result;
	}

	/**
	 * Call a method on the source class and cache it If it's already cached, return the cached result instead.
	 *
	 * @param  string $name The method name
	 * @param  array $arguments Array of parameters included.
	 * @return mixed
	 */
	function call_source_and_cache( $name, $arguments ) {
		$key           = $this->get_key_for_call( $name, $arguments );
		$cached_result = false;

		if ( false === $this->should_override_cache() ) {
			$cached_result = $this->get_cached_result( $key );
		}

		if ( false === $cached_result ) {
			$result = $this->call_source( $name, $arguments );
			$this->cache_result( $key, $result );
			return $result;
		} else {
			return $cached_result;
		}
	}

	/**
	 * Call a method on the source class.
	 *
	 * @param  string $name The method name
	 * @param  array $arguments Array of parameters included.
	 * @return mixed
	 */
	function call_source( $name, $arguments ) {
		$signature = array( $this->source, $name );

		if ( ! method_exists( $this->source, $name ) ||
			 ! is_callable( $signature )
		) {
			return;
		}

		return call_user_func_array(
			array( $this->source, $name ),
			$arguments
		);
	}

	/**
	 * Cache results of a method call.
	 *
	 * @param string $key The cache key
	 * @param mixed $result The result data to cache as.
	 */
	function cache_result( $key, $result ) {
		if ( ! $this->can_cache_results() ) {
			return;
		}

		// If the result is empty or an error, don't cache the data. We don't want to cache empty data because it could be incorrect.
		if (
			empty( $result ) ||
			is_wp_error( $result ) ||
			is_a( $result, 'Exception' )
		) {
			return;
		}

		set_transient( $key, json_encode( $result ), $this->get_cache_duration() );
	}

	/**
	 * Attempt to retrieve data from the cache.
	 *
	 * @param  string $key The cache key
	 * @return mixed
	 */
	function get_cached_result( $key ) {
		if ( $this->can_cache_results() ) {
			$json = get_transient( $key );

			if ( false !== $json ) {
				return json_decode( $json, true );
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Get the cache key for a method call.
	 *
	 * @param  string $name The method name
	 * @param  array $arguments Array of parameters included.
	 * @return string A MD5 hash representing the function call and paramaters.
	 */
	function get_key_for_call( $name, $arguments ) {
		$key  = get_class( $this->source );
		$key .= ';';
		$key .= $name;
		$key .= ';';
		$key .= json_encode( $arguments, JSON_NUMERIC_CHECK ); // Using the JSON_NUMERIC_CHECK option because the cache key will be different if an argument is passed in as "123" vs. 123.

		return md5( $key );
	}

	/**
	 * Get the duration for a transient.
	 *
	 * @return int
	 */
	public function get_cache_duration() {
		// Don't allow the cache duration to be 0. Transients with no expiration are autoloaded on every request whether they're needed or not.
		if ( 0 === intval( $this->cache_duration ) ) {
			$this->cache_duration = MONTH_IN_SECONDS;
		}

		return intval( $this->cache_duration );
	}

	/**
	 * Determine if we can cache results.
	 *
	 * @return boolean
	 */
	function can_cache_results() {
		if ( ! defined( 'PHPUNIT_RUNNER' ) ) {
			if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * Determine if we should override the cache for a single method call.
	 *
	 * @return boolean
	 */
	public function should_override_cache() {
		return filter_var( $this->override_cache, FILTER_VALIDATE_BOOLEAN );
	}
}
