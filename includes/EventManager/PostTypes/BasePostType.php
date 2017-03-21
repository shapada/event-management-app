<?php

namespace EventManager\PostTypes;

/**
 * Base class for post types.
 */
abstract class BasePostType {

	/**
	 * Get the post type name.
	 *
	 * @return string
	 */
	abstract public function get_name();

	/**
	 * Get the singular post type label.
	 *
	 * @return string
	 */
	abstract public function get_singular_label();

	/**
	 * Get the plural post type label.
	 *
	 * @return string
	 */
	abstract public function get_plural_label();

	/**
	 * The meta key for the filetype field
	 */
	public $filetype_meta_key = 'event_manager_filetypes';

	/**
	 * Indicator for whether to automatically load the filetype field
	 */
	public $autoload_filetype_field = true;

	/**
	 * The various types of files
	 */
	public $file_types = array(
		'excel'      => 'Excel',
		'pdf'        => 'PDF',
		'powerpoint' => 'PowerPoint',
		'video'      => 'Video',
		'word'       => 'Word',
		'other'      => 'Other',
	);

	/**
	 * @var array Components assigned to this post type.
	 */
	public $components = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {}

	/**
	 * Register the post type class.
	 */
	public function register() {
		$post_type_name = $this->get_name();

		add_action(
			'init',
			array( $this, 'register_post_type' )
		);
		add_action( 'save_post', [ $this, 'save_custom_filetype_fields' ], 1, 3 );

		if( true === $this->autoload_filetype_field ) {
			add_action( 'edit_form_after_title', [ $this, 'add_filetype_field' ], 10 );
		}
	}

	/**
	 * Register the post type.
	 */
	public function register_post_type() {
		\register_post_type(
			$this->get_name(), $this->get_options()
		);
	}

	/**
	 * @param $component Components\AbstractComponent
	 */
	public function register_component( $component ) {
		    $component->register();
		    $this->components[] = $component;
    }

	/**
	 * Get post type labels.
	 *
	 * @return array
	 */
	public function get_labels() {
		$labels = $this->get_default_labels();
		return apply_filters( "event_manager_filter_{$this->get_name()}_post_type_labels", $labels );
	}

	/**
	 * Get the default post type labels.
	 *
	 * @return array
	 */
	public function get_default_labels() {
		$plural_label   = $this->get_plural_label();
		$singular_label = $this->get_singular_label();

		return array(
			'name'               => $plural_label,
			'singular_name'      => $singular_label,
			'all_items'          => sprintf( 'All %s', $plural_label ),
			'add_new_item'       => sprintf( 'Add New %s', $singular_label ),
			'edit_item'          => sprintf( 'Edit %s', $singular_label ),
			'new_item'           => sprintf( 'New %s', $singular_label ),
			'view_item'          => sprintf( 'View %s', $singular_label ),
			'search_items'       => sprintf( 'Search %s', $plural_label ),
			'not_found'          => sprintf( 'No %s found.', strtolower( $plural_label ) ),
			'not_found_in_trash' => sprintf( 'No %s found in Trash.', strtolower( $plural_label ) ),
			'parent_item_colon'  => sprintf( 'Parent %s:', $plural_label ),
		);
	}

	/**
	 * Get the post type options.
	 *
	 * @return array
	 */
	public function get_options() {
		$options = $this->get_default_options();
		return apply_filters( "event_manager_filter_{$this->get_name()}_post_type_options", $options );
	}

	/**
	 * Get the default post type options.
	 *
	 * @return array
	 */
	public function get_default_options() {
		return array(
			'labels'       => $this->get_labels(),
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
		);
	}

	/**
	 * Get the label bookmarks referencing this post type should use.
	 *
	 * @return string
	 */
	public function get_bookmark_type_label() {
		return $this->get_plural_label();
	}

	/**
	 * Save the custom metadata as filetype url
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function save_custom_filetype_fields( $post_id, $post, $update ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check for link URL and save
		$passed_filetype_value = '';

		if( isset( $_POST[ $this->filetype_meta_key ] ) ) {
			$passed_filetype_value = $_POST[ $this->filetype_meta_key ];

			if( ! empty( $passed_filetype_value ) ) {
				update_post_meta( $post_id, $this->filetype_meta_key, sanitize_text_field( $passed_filetype_value ) );
			}
		}

		return;
	}

	/**
	 * Checks if the current post type supports 'filetype' and adds the metabox
	 */
	public function add_filetype_field() {
		global $post;

		if( post_type_supports( $post->post_type, 'filetype' ) ) {
			$this->display_filetype_metabox( $post );
		}
	}

	/**
	 * Output the filetype metabox
	 * @param $post
	 * @param $metabox
	 */
	public function display_filetype_metabox( $post ) {

		if( $this->get_name() !== $post->post_type ) {
			return;
		}

		$filetype_value = $this->get_filetype_value( $post->ID );

		?>
		<div id="filetype-holder">
			<ul>
				<?php
				foreach( $this->file_types as $key => $filetype ) { ?>
					<li class="form-<?php echo esc_attr( $key );?><?php echo $key === $filetype_value ? ' current' : ''; ?>" data-filetype="<?php echo esc_attr( $key );?>">
						<a href="#">
							<span class="<?php echo 'icon filetype-' . esc_attr( $key ); ?>"></span>
							<?php echo esc_html( $filetype ); ?>
						</a>
					</li>
					<?php
				}

				// Add some spacers - empty <li> to even out the spacing when it wraps
				$count = count($this->file_types);
				for ($i = 2; $i < $count; $i++) { ?>
					<li></li>
				<?php }

				?>
			</ul>
		</div>

		<select
			id="<?php echo esc_attr( $this->filetype_meta_key ); ?>"
			name="<?php echo esc_attr( $this->filetype_meta_key ); ?>" >
			<option value=""></option>
			<?php
			foreach( $this->file_types as $key => $file_type ) { ?>
				<option <?php selected( $key, $filetype_value, true );?> value="<?php echo esc_attr( $key );?>"><?php echo esc_html( $file_type );?></option>
				<?php
			}
			?>
		</select>
		<?php
			do_action( 'display_filetype_field' );
		return true;
	}

	/**
	 * Get the filetype value for a passed post id
	 * @param $post_id
	 *
	 * @return mixed|void
	 */
	public function get_filetype_value( $post_id ) {

		if( ! isset( $post_id ) || ! is_numeric( $post_id ) ) {
			return;
		}

		return \get_post_meta( intval( $post_id ), $this->filetype_meta_key, true );
	}
}
