<?php

namespace EventManager\Refinement;

abstract class AbstractFacets {

	/*
	 * The various facets that data will be retrieved for filtering. Add all additional facets to this array
	 */
	protected $facets;

	protected $data_source = '';

	public function __construct( $data_source, $passed_facets = array() ) {
		$this->data_source = $data_source;
		$this->facets = $passed_facets;
	}

	// Register any hooks
	public function register() {

	}

	/**
	 * Output the facets that have been defined in this class.
	 *
	 * @param null|bool $facet_slug 	Specify a singular slug to output, instead of all facets.
	 * @param string	$class 			Specify a class name for the element.
	 * @param bool 		$show_label 	Option to show label with facets, defaults to true.
	 */
	public function output_refinement_facets( $facet_slug = null, $class = '', $show_label = true ) {

		?>
		<div id="refinements" data-source="<?php echo esc_attr( $this->data_source ) ?>">
		<?php

		// Loop through each of the facets and output the appropriate options.
		foreach ( $this->facets as $facet ) {
			$items  = isset( $facet['items'] ) ? $facet['items'] : null;
			$args   = isset( $items['args'] ) ? $items['args'] : array();
			$title  = isset( $facet['title'] ) ? $facet['title'] : null;
			$format = isset( $facet['format'] ) ? $facet['format'] : 'checkbox';
			$default = isset( $facet['default'] ) ? $facet['default'] : '';

			if ( ! empty( $facet_slug ) && $facet_slug !== $items['slug'] ) {
				continue;
			}

			switch ( $items['type'] ) {
				case 'taxonomy':
					$terms = array();

					// Grab the terms for the taxonomy
					$taxonomy_args = array(
						'taxonomy'   => $items['slug'],
					);

					$taxonomy_args = array_merge( $taxonomy_args, $args );

					$taxonomy_args = apply_filters( 'event_manager_filter_facet_taxonomy_args', $taxonomy_args, $items['slug'], get_class( $this ) );

					$terms = get_terms( $taxonomy_args );

					$taxonomy = get_taxonomy( $items['slug'] );

					if ( ! is_wp_error( $terms ) &&
						! is_wp_error( $taxonomy ) ) {
						$this->output_terms( $taxonomy, $terms, $title, $format, $class, $show_label, $default );
					}

					break;

				case 'post_types':
					$this->output_post_types( $items['slug'], $title, $format, $class, $show_label );

					break;

				case 'custom':
					$class .= ' facet-custom';
					$this->output_custom_field( $items['slug'], $title, $format, $class, $items );
					break;

				default:
					break;
			}
		}
		?>
		</div>
		<?php

	}

	/**
	 * Output the facet options for a passed set of terms
	 * @param $taxonomy
	 * @param $terms
	 * @param $title - title for grouping
	 * @param $format - format of output (checkbox, select)
	 * @param $class - class to add to HTML element
	 * @param $show_label - should the label (heading) be shown
	 */
	public function output_terms( $taxonomy, $terms, $title, $format, $class, $show_label = true, $default = '' ) {

		if ( empty( $terms ) ||
			empty( $taxonomy ) ) {
			return;
		}
		?>

		<?php if ( $show_label ) { ?>
			<h3 class="sidebar-heading"><strong><?php echo esc_html( $title );?></strong></h3>
		<?php } ?>

		<div class="facets">
		<?php

		switch ( $format ) {
			case 'checkbox': ?>
				<ul class="facet-terms"><?php

				// Output each taxonomy term as checkbox
				foreach ( $terms as $term ) {

					$term_name = $term->taxonomy . '-' . $term->slug;

					switch ( $format ) {
						case 'checkbox':
							$this->output_checkbox_field(
								$term_name,
								$term->term_id,
								$term->name,
								array(
									'taxonomy'  => $taxonomy->name,
									'term'      => $term->term_id
								)
							);
							break;
					}
				}
				?>
				</ul>
				<?php if ( 3 < count( $terms ) ) { ?>
					<div class="more-facets"><span class="more">+ More</span><span class="less">- Less</span> <?php echo esc_attr( $title );?></div>
				<?php }
				break;
			case 'select': ?>
				<?php $class .= ' select2-dropdown'; ?>
				<div>
					<select name="terms-<?php echo esc_attr( $taxonomy->name );?>" id="terms-<?php echo esc_attr( $taxonomy->name );?>" class="select2-dropdown <?php echo esc_attr( $class ); ?>">

						<?php
						$terms = apply_filters( "event_manager_filter_refinement_select_terms_{$taxonomy->name}", $terms, $taxonomy );

						$this->output_option_field(
							0,
							'Any',
							array(
								'taxonomy'  => $taxonomy->name,
							)
						);

						// Output each taxonomy term as an option
						foreach( (array) $terms as $key => $term ) {
							if ( is_array( $term ) && ! is_a( $term, 'WP_Term' ) ) {
								$this->output_option_group(
									$key,
									$term,
									$taxonomy
								);
							} else {
								$this->output_option_field(
									$term->term_id,
									$term->name,
									array(
										'taxonomy'  => $taxonomy->name,
										'term'      => $term->term_id
									),
									$default
								);
							}
						} ?>
					</select>
				</div>
				<?php
				break;
		} ?>
		</div>
	<?php
	}

	/**
	 * Output data refinement fields for input fields
	 * @param $data_refinement
	 */
	public function output_data_refinement( $data_refinement ) {

		if( empty( $data_refinement ) ) {
			return;
		}

		foreach( $data_refinement as $refinement_type => $refinement ) { ?>
			data-refinement-<?php echo esc_attr( $refinement_type ); ?>="<?php echo esc_attr( $refinement ); ?>"<?php
		}

	}

	public function output_option_field( $value, $label, $data_refinement, $default = '' ) {
		$selected = $this->is_selected_facet( $data_refinement );
		if ( ! $selected && ! empty( $default ) ) {
			$selected = ( absint( $value)  === absint($default) ) ? true : false;
		}
		?>
		<option value="<?php echo esc_attr( $value );?>"
			<?php
			$this->output_data_refinement( $data_refinement );
			?>
			<?php selected( $selected ); ?>>
			<?php echo esc_html( $label );?></option>
		<?php

	}

	/**
	 * Render a select field optgroup.
	 *
	 * @param  string $label The optgroup label
	 * @param  array $terms The terms for the optgroup
	 * @param  \stdClass $taxonomy The taxonomy object
	 */
	public function output_option_group( $label, $terms, $taxonomy ) { ?>
		<optgroup label="<?php echo esc_attr( $label ) ?>">
			<?php
			foreach ( (array) $terms as $term ) {
				if ( ! is_a( $term, 'WP_Term' ) ) {
					continue;
				}

				$this->output_option_field(
					$term->term_id,
					$term->name,
					array(
						'taxonomy'  => $taxonomy->name,
						'term'      => $term->term_id
					)
				);
			} ?>
		</optgroup>
	<?php
	}

	/**
	 * Output a checkbox field based on passed parameters
	 * @param $id
	 * @param $value
	 * @param $label
	 * @param $data_refinement
	 */
	public function output_checkbox_field( $id, $value, $label, $data_refinement ) {
		$checked = $this->is_selected_facet( $data_refinement ); ?>
		<li class="facet-item">
			<input type="checkbox"
					name="<?php echo esc_attr( $id );?>"
					value="<?php echo esc_attr( $value );?>"
					id="<?php echo esc_attr( $id );?>"
					<?php
					$this->output_data_refinement( $data_refinement );
					?>
					<?php checked( $checked ); ?> />
					<?php
					// deal with words/strung&together.with@non-word-characters
					$label= apply_filters( 'event-manager_nav_title', $label ); ?>
			<label for="<?php echo esc_attr( $id );?>"><?php echo esc_html( $label ); ?></label>
		</li>
		<?php
	}


	/**
	 * Output an unsorted list of post type input checkboxes for a past array of post types.
	 * @param $post_types
	 *
	 * @return bool
	 */
	public function output_post_types( $post_types, $title, $format, $class, $show_label = true ) {

		if( empty( $post_types ) ) {
			return false;
		}

		// Output the list of post types
		$id = 'post-types-';

		// Create an an based on the passed slugs
		foreach( $post_types as $slug ) {
			$id .= $slug;
		}

		?>

		<?php if ( $show_label ) { ?>
			<h3 class="sidebar-heading"><strong><?php echo esc_html( $title );?></strong></h3>
		<?php } ?>

		<div class="facets"><?php

		switch( $format ) {
			case 'checkbox': ?>
				<ul class="facet-post-types facet-list <?php echo esc_attr( $class ); ?>"><?php

					foreach( (array) $post_types as $post_type ) {
						$post_type_details = get_post_type_object( $post_type );

						if( ! empty( $post_type_details ) ) {
							$this->output_checkbox_field(
								$post_type,
								$post_type,
								$post_type_details->labels->singular_name,
								array(
									'post-type' => $post_type
								)
							);
						}
					}
					?>
				</ul>
				<?php if ( 3 < count( $post_types ) ) { ?>
					<div class="more-facets"><span class="more">+ More</span><span class="less">- Less</span> Types</div>
				<?php }
				break;
			case 'select': ?>
				<div>
					<select name="<?php echo esc_attr( $id );?>" id="<?php echo esc_attr( $id ); ?>" class="select2-dropdown">
						<?php

						// Output empty field
						$this->output_option_field(
							'',
							'',
							array(
								'post-type'  => '',
							));

						// Loop through the post types and add as an option
						foreach( (array) $post_types as $post_type ) {
							$post_type_details = get_post_type_object( $post_type );

							if( ! empty( $post_type_details ) ) {

								// value, label, data-refinement

								$this->output_option_field(
									$post_type,
									$post_type_details->labels->singular_name,
									array(
										'post-type' => $post_type
									)
								);
							}
						}

						?>

					</select>
				</div>
				<?php

				break;
		} ?>
		</div>
		<?php
	}

	/**
	 * Output a custom input field in the facet list.
	 */
	public function output_custom_field( $name, $title, $format, $class, $items = array() ) {

		if ( empty( $name ) || empty( $items ) ) {
			return false;
		}

		$value = ! empty( $items['value'] ) ? $items['value'] : '';
		$id    = 'custom-facet-' . $name;
		$data  = array( 'custom' => $name ); ?>

		<div class="facets-hidden">
			<?php
			switch ( $format ) {
				case 'hidden':
					$class .= ' -hidden'; ?>

					<div class="<?php echo esc_attr( $class ); ?>">
						<input
							type="hidden"
							id="<?php echo esc_attr( $id ); ?>"
							name="<?php echo esc_attr( $name ) ?>"
							value="<?php echo esc_attr( $value ) ?>"
							<?php $this->output_data_refinement( $data ); ?> />
					</div>

					<?php
					break;

				default:
					break;
			} ?>
		</div>
		<?php
	}

	public function get_selected_facets() {
		$selected_facets = array();

		global $wp_query;

		// Post types are handled separately, we add them to query vars so we can access them later.
		if ( isset( $_GET['event-manager-post-types'] ) ) {
			$wp_query->query_vars['event-manager-post-types'] = sanitize_text_field( $_GET['event-manager-post-types'] );
		}

		// Determine if a user ID was passed in.
		if ( isset( $_GET['user-id'] ) ) {
			$wp_query->query_vars['user-id'] = sanitize_text_field( $_GET['user-id'] );
		}

		$search_term = \EventManager\Helpers\get_search_query();

		// Include the search query, *even if it's empty*.
		$wp_query->query_vars['s'] = sanitize_text_field( $search_term );

		foreach ( $this->facets as $facet ) {
			if ( 'post_types' === $facet['items']['type'] ) {
				$slug = 'event-manager-post-types';
			} else {
				$slug = $facet['items']['slug'];
			}

			if ( isset( $wp_query->query_vars[ $slug ] ) ) {
				$selected_facets[ $slug ] = explode( ',', $wp_query->query_vars[ $slug ] );
			} else if ( ! empty( $facet['default'] ) && ! isset( $wp_query->query_vars[ $slug ] ) ) {
				/**
				 * Only apply defaults on non rest requests.
				 *
				 * There does not seem to be a non convoluted way of doing
				 * handling requests like "any" in the location. So the easiest
				 * way is to only apply defaults on non rest requests. A rest request
				 * will have any needed defaults applied though selected facets, and
				 * defaults won't be improperly applied.
				 */
				if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
					continue;
				}

				$selected_facets[ $slug ] = (array) $facet['default'];
			}
		}


		return $selected_facets;
	}

	/**
	 * Check if a specific refinement value is currently selected (present in the URL).
	 * @param $data_refinement
	 * @return bool True if the value is present, false if not.
	 */
	public function is_selected_facet( $data_refinement ) {
		$selected_facets = $this->get_selected_facets();

		if ( isset( $data_refinement['post-type'] ) ) {
			$post_type = $data_refinement['post-type'];

			if ( empty( $post_type ) ) {
				return false;
			}

			foreach ( (array) $selected_facets as $slug => $facet ) {
				if ( ! 'event-manager-post-types' === $slug ) {
					continue;
				}

				if ( in_array( $post_type, (array) $facet ) ) {
					return true;
				}
			}

		} elseif ( isset( $data_refinement['taxonomy'] ) ) {
			$taxonomy = ! empty( $data_refinement['taxonomy'] ) ? $data_refinement['taxonomy'] : null;
			$term     = ! empty( $data_refinement['term'] ) ? $data_refinement['term'] : null;

			if ( empty( $taxonomy ) || empty( $term ) ) {
				return false;
			}

			foreach ( (array) $selected_facets as $slug => $facet ) {
				if ( $taxonomy !== $slug ) {
					continue;
				}

				if ( in_array( $term, (array) $facet ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Gets the facets
	 * @return array An array of facets
	 */
	public function get_facets() {
		return $this->facets;
	}

	/**
	 * do facets need to be hidden?
	 * @return bool
	 */
	public function facets_need_hiding(){
		//if all elements in the facets are hidden, facets could be hidden
		$custom_hidden_facets = array_filter( $this->facets, array( $this, 'facet_is_hidden') );
		return sizeof($custom_hidden_facets) === sizeof($this->facets);
	}

	public function facet_is_hidden( $facet )
	{
		return 'hidden' === \EventManager\Helpers\get_property('format', $facet );
	}

}
