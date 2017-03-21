<?php

namespace EventManager\PostTypes\Components;

abstract class AbstractComponent {

	public $field_name;
	public $field_title;
	public $field_priority;
	public $required            = false;
	public $component_location  = 'normal';
	public $save_type           = 'update';

	/**
	 * @var string
	 */
	protected $post_type = null;

	/**
	 * AbstractComponent constructor.
	 *
	 * @param string       $field_name
	 * @param string       $field_title
	 * @param string       $field_priority
	 * @param bool         $required
	 * @param string       $post_type
	 */
	public function __construct( $field_name, $field_title, $field_priority = 'normal', $required = false, $post_type = null ) {

		if( ! empty( $field_name ) ) {
			$this->field_name = $field_name;
		}

		if( ! empty( $field_title ) ) {
			$this->field_title = $field_title;
		}

		if( ! empty( $field_priority ) ) {
			$this->priority = $field_priority;
		}

		if( ! empty( $required ) ) {
			$this->required = (bool) $required;
		}
		
		if ( ! empty( $post_type ) ) {
			$this->post_type = $post_type;
		}

	}

	/**
	 * Display the inner value of the meta box
	 */
	public abstract function display_meta_box();

	/**
	 * Register hooks and actions for this class
	 */
	public function register() {
		if ( ! empty( $this->post_type ) ) {
			add_action( "add_meta_boxes_{$this->post_type}", [ $this, 'add_component_meta_box' ], $this->priority );
		} else {
			add_action( "add_meta_boxes", [ $this, 'add_component_meta_box' ], $this->priority );
		}

		add_action( 'save_post', [ $this, 'save_custom_fields' ], 1, 3 );
	}

	/**
	 * Add the meta box to the admin page
	 */
	public function add_component_meta_box() {
		\add_meta_box(
			'metabox_' . $this->get_name(),
			$this->get_title(),
			[ $this, 'display_meta_box' ],
			$this->post_type,
			$this->component_location,
			'default'
		);
	}

	/**
	 * Retrieve the value for the field
	 * @return string
	 */
	public function get_value() {

		$post_id = get_the_ID();
		$value   = '';

		if( ! empty( $post_id ) ) {
			$value = \get_post_meta( $post_id, $this->get_name(), true );
		}

		return $value;

	}

	/**
	 * Get the name of this field
	 * @return mixed
	 */
	public function get_name() {
		return $this->field_name;
	}

	/**
	 * Get the title of this field
	 * @return mixed
	 */
	public function get_title() {
		return $this->field_title;
	}

	/**
	 * Save the custom metadata as link url
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function save_custom_fields( $post_id, $post, $update ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if( empty( $post_id ) ) {
			return;
		}

		if( isset( $_POST[ $this->get_name() ] ) ) {
			$passed_value = $_POST[ $this->get_name() ];

			if( ! empty( $passed_value ) ) {
				if( 'update' === $this->save_type ) {
					update_post_meta( $post_id, $this->get_name(), sanitize_text_field( $passed_value ) );
				}
				else {
					add_post_meta( $post_id, $this->get_name(), sanitize_text_field( $passed_value ) );
				}
			}
			else {
				delete_post_meta( $post_id, $this->get_name() );
			}

		}

		return;
	}

}
