<?php

namespace EventManager\PostTypes;

abstract class AbstractVenuePostType extends BasePostType {

	/**
	 * Get the data source slug. We use this to prefix meta keys and permalinks.
	 *
	 * @return string
	 */
	abstract public function get_data_source();

	/**
	 * Get the singular post type label.
	 *
	 * @return string
	 */
	public function get_singular_label() {
		return 'Venue';
	}

	/**
	 * Get the plural post type label.
	 *
	 * @return string
	 */
	public function get_plural_label() {
		return 'Venues';
	}

	/**
	 * Register hooks and actions
	 */
	public function register() {
		parent::register();

		add_filter(
			"event_manager_filter_{$this->get_name()}_post_type_options",
			[ $this, 'post_type_options' ]
		);
	}

	/**
	 * Custom options for the custom post type
	 * @return array
	 */
	public function post_type_options() {
		$defaults = parent::get_default_options();

		$custom = array(
			'labels'       => $this->get_labels(),
			'public'       => true,
			'show_ui'      => true,
			'menu_icon'      => 'dashicons-admin-multisite',
			'supports'     => array(
				'author',
				'title',
				'editor',
				'thumbnail',
				'revisions',
			),
			'rewrite'      => array(
				'slug' => $this->get_data_source()
			),
		);

		return array_merge( (array) $defaults, (array) $custom );
	}
}
