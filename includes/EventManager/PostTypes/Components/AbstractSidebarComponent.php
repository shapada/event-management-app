<?php

namespace EventManager\PostTypes\Components;

abstract class AbstractSidebarComponent extends AbstractComponent {

	public $data_slug;          // Meta key or post type
	public $data_type;          // post_meta or post_type
	public $fields;
	public $priority;           // Priority for the sidebar component
	public $component_location = 'side';

	/** Function should return an array with the following data:
	array(
	'data_type' => 'post_meta' OR 'post_type',
	'data_slug' => Unique slug for the metadata or post type,
	);
	 * @return mixed
	 */
	abstract public function get_component_settings();

	/**
	 * Function should return an array of fields in the format
	 * 'field_id' => 'Field Title'
	 * @return mixed
	 */
	abstract public function get_fields();

	/**
	 * AbstractSidebarComponent constructor.
	 *
	 * @param int    $priority
	 * @param string $post_type
	 */
	public function __construct( $priority = 10, $post_type = null ) {
		$this->priority = intval( $priority );

		if ( ! empty( $post_type ) ) {
			$this->post_type = $post_type;
		}
	}

	public function register() {

		parent::register();

		// Grab the component settings.
		$component_settings = $this->get_component_settings();

		if( is_array( $component_settings ) ) {

			if( isset( $component_settings['data_type'] ) ) {

				// Make sure this is a valid value.
				if( in_array( $component_settings['data_type'] , array(
						'post_meta',
						'post_type' )
				) ) {
					$this->data_type = $component_settings['data_type'];
				}
			}

			if( isset( $component_settings['data_slug'] ) ) {
				$this->data_slug = $component_settings['data_slug'];
			}
		}

		$this->fields = $this->get_fields();
	}

	/**
	 * Outputs the meta box and fields that are needed for the sidebar component.
	 */
	public function display_meta_box() { ?>
        <div class="sidebar_fields">
			<?php
			$this->display_fields();
			?>
			<?php wp_nonce_field( $this->get_name() . '_nonce', $this->get_name() . '_nonce' ); ?>
            <button class="button sidebar_button_add" data-sidebar-id="<?php echo esc_attr( $this->get_name() ); ?>">Add</button>
        </div>

        <div class="sidebar_saved_items">
			<?php
			// Display the items, data will be escaped in the function.
			echo $this->display_saved_items();
			?>
        </div>
		<?php

		return;
	}

	/**
	 * Displays the empty form input fields that have been set in the 'fields' variable. This function can be overridden
	 * if specific output needs are required or the hook can be used.
	 */
	public function display_fields() {

		// If no fields are defined, don't display
		if( empty( $this->fields ) ) {
			return;
		}

		$field_prefix = $this->get_name();

		/**
		 * Output the fields. We prefix each field with the metabox slug, to prevent duplicates field names.
		 **/
		foreach ( $this->fields as $key => $field_name ) {
			$field_id = $field_prefix . '_' . $key; ?>
            <p>
                <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $field_name ); ?></label><br />
                <input type="text"
                       id="<?php echo esc_attr( $field_id ); ?>"
                       name=""
                       data-target="<?php echo esc_attr( $key ); ?>"
                       value="" />
            </p>
		<?php }

		// Output the JS template.
		echo $this->underscore_template();

		do_action( 'sidebar_component_' . $this->data_slug . '_fields' );

		return;
	}

	/**
	 * Get the underscore JS template. By default all side metaboxes use link/titles unless overwritten.
	 */
	public function underscore_template() {
		ob_start();
		$field_prefix = $this->get_name(); ?>
        <script type="text/template" id="<?php echo esc_attr( $field_prefix ); ?>-template">
            <li>
                <i class="dashicons dashicons-edit"></i>
                <a href="<%=link%>" target="_blank"><%-title%></a>
                <div class="fields-wrapper">
                    <input type="text" name="<?php echo esc_attr( $field_prefix ); ?>_title[]" value="<%=title_field_value%>" />
                    <input type="text" name="<?php echo esc_attr( $field_prefix ); ?>_link[]" value="<%=link_field_value%>" />
                </div>
                <i class="dashicons dashicons-no"></i>
            </li>
        </script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Output items that have already been saved.
	 */
	public function display_saved_items() {
		$values = $this->get_value();
		$data_name = $this->get_name(); ?>
        <ul>
			<?php
			if ( empty( $values ) ) {
				echo '</ul>'; // We need the list to append new items in JS.
				return;
			}
			/**
			 * Output the array of links and titles.
			 **/
			foreach ( (array) $values as $key => $value ) {
				if ( empty ( $values[ $key ]['title'] ) && empty( $values[ $key ]['link'] ) ) {
					continue;
				} ?>
                <li>
                    <i class="dashicons dashicons-edit"></i>
                    <a href="<?php echo esc_url( $values[ $key ]['link'] ); ?>" target="_blank"><?php echo esc_html( $values[ $key ]['title'] ); ?></a>
                    <div class="fields-wrapper">
                        <input type="text" name="<?php echo esc_attr( $data_name ); ?>_title[]" value="<?php echo esc_attr( $values[ $key ]['title'] ); ?>" />
                        <input type="text" name="<?php echo esc_attr( $data_name );; ?>_link[]" value="<?php echo esc_attr( $values[ $key ]['link'] ); ?>" />
                    </div>
                    <i class="dashicons dashicons-no"></i>
                </li>
			<?php } ?>
        </ul>
	<?php }

	/**
	 * Save the custom metadata for title and links.
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function save_custom_fields( $post_id, $post, $update ) {
		$meta_box_name = $this->get_name();

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( empty( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST[ $meta_box_name . '_nonce' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ $meta_box_name . '_nonce' ], $meta_box_name . '_nonce' ) ) {
			return;
		}

		$values = array();

		/**
		 * Handle saving of title and links.
		 * The fields are saved together in an array e.g. array( array( 'title' => 'Title 1', 'link' => 'Link 1' ) ).
		 **/
		if ( isset( $_POST[ $meta_box_name . '_title' ] ) ) {
			foreach ( (array) $_POST[ $meta_box_name . '_title' ] as $key => $post_value ) {
				if ( isset( $_POST[ $meta_box_name . '_title' ][ $key ] ) && isset( $_POST[ $meta_box_name . '_link' ][ $key ] ) ) {
					$values[] = array( 'title' => sanitize_text_field( $_POST[ $meta_box_name . '_title' ][ $key ] ), 'link' => sanitize_text_field( $_POST[ $meta_box_name . '_link' ][ $key ] ) );
				}
			}
		}

		if ( ! empty( $values ) ) {
			update_post_meta( $post_id, $meta_box_name, $values );
		} else {
			delete_post_meta( $post_id, $meta_box_name );
		}
	}

	/**
	 * Returns the data slug for the sidebar component.
	 * @return string Sidebar slug.
	 */
	public function get_name() {
		return $this->data_slug;
	}

}

