<?php

namespace EventManager\DataTables;

use EventManager\Utils\Iterators\WPQueryIterator;
use EventManager\TemplateTags;

/**
 * Abstract class for a WP_Query-powered data table.
 */
abstract class AbstractWPQueryDataTable extends AbstractDataTable {

	protected $iterator;
	protected $query_args = array();
	protected $selected_facets;

	public function __construct( $query_args = array() ) {
		$this->query_args = $query_args;
	}

	public function register() {
		parent::register();
	}

	/**
	 * Get the table rows.
	 *
	 * @return array
	 */
	public function get_rows() {
		if ( ! $this->has_iterator() ) {
			$this->iterator = new WPQueryIterator( $this->get_query_args() );
		}

		return $this->iterator->get_items();
	}

	/**
	 * Set the query args for the table.
	 *
	 * @param array $query_args
	 */
	public function set_query( $query_args ) {
		$this->iterator = new WPQueryIterator( $query_args );
	}

	public function get_query_args() {
		$args = $this->query_args;

		if ( empty( $args ) ) {
			$args = $this->get_default_query_args();
		}

		return $args;
	}

	public function set_query_args( $query_args = array() ) {
		if ( empty( $query_args ) ) {
			$query_args = $this->get_query_args();
		}

		if ( empty( $query_args ) ) {
			$query_args = $this->get_default_query_args();
		}

		$this->query_args = $query_args;
	}

	public function set_facets( $facets ) {
		$this->selected_facets = $facets->get_selected_facets();
	}

	public function get_default_query_args() {
		$args = array(
			'post_type' => 'post',
			'no_found_rows' => false,
		);

		// Use Elasticsearch for this query, if available.
		if ( $this->should_use_elastic_search() ) {
			$args['ep_integrate'] = true;
		} else {
			$args['ep_integrate'] = false;
		}

		if ( ! empty( $_GET['currentPage'] ) ) {
			$args['paged'] = absint( $_GET['currentPage'] );
		}

		if ( ! empty( $_GET['search'] ) ) {
			$args['s'] = sanitize_text_field( $_GET['search'] );
		}

		$tax_query = array();

		foreach ( (array) $this->selected_facets as $slug => $facet ) {
			if ( empty( $facet ) ) {
				continue;
			}

			if ( 'event-manager-post-types' === $slug ) {
				$args['post_type'] = $facet;
			} elseif ( 'user-id' === $slug ) {
				// Do nothing yet if a user ID is passed in. This should facet should be handled in the corresponding data table class because it's use can vary.
			} else {
				$tax_query[] = array(
					'taxonomy' 	=> $slug,
					'terms' 	=> $facet,
					'field'		=> 'term_id',
				);
			}
		}

		if ( ! empty( $tax_query ) ) {
			$args['tax_query'] = $tax_query;
		}

		return $args;
	}

	/**
	 * Get the query for the table.
	 *
	 * @return \WP_Query|null
	 */
	public function get_query() {
		if ( ! $this->has_iterator() ) {
			return null;
		}

		return $this->iterator->get_query();
	}

	/**
	 * Determine if the table has a valid iterator.
	 *
	 * @return boolean
	 */
	public function has_iterator() {
		return is_a( $this->iterator, 'EventManager\Utils\Iterators\WPQueryIterator' );
	}

	/**
	 * Render the table's rows.
	 */
	public function render_rows() {
		if ( ! $this->has_iterator() ) {
			$this->iterator = new WPQueryIterator( $this->get_query_args() );
		}

		$this->iterator->loop( array( $this, 'render_row' ) );
	}

	/**
	 * Render the title row item.
	 *
	 * @param  \WP_Post $row The row data.
	 * @param  \EventManager\DataTables\Column $column The column this item belongs to.
	 */
	public function render_title_item( \WP_Post $row, \EventManager\DataTables\Column $column ) {
		if ( ! is_a( $row, 'WP_Post' ) ) {
			$this->render_default_item( $row, $column );
		}

		$classes = $this->get_row_classes( $row, $column ); ?>

		<a href="<?php echo esc_url( get_the_permalink( $row ) ) ?>" <?php if ( ! empty( $classes ) ) { echo 'class="' . esc_attr( $classes ) . '"'; } if ( $this->open_title_link_in_new_tab( $row ) ) { ?> target="_blank" <?php } ?> >
			<?php echo esc_html( get_the_title( $row ) ) ?>
		</a>

	<?php
	}


	/**
	 * Returns a flag that verifies if a title link needs to open in a new tab.
	 * @param \WP_Post $row
	 *
	 * @return bool
	 */
	public function open_title_link_in_new_tab( \WP_Post $row ) {
		//Currently, for the link post types retrieve the "open in new tab" flag
		if ( $row->post_type === event_manager_core()->education->education_link_cpt->post_type_slug ) {
			return event_manager_core()->education->education_link_cpt->open_link_in_new_tab( $row->ID );
		} elseif ( $row->post_type === event_manager_core()->resources->links->post_type_slug ) {
			return event_manager_core()->resources->links->open_link_in_new_tab( $row->ID );
		}

		return false;
	}
	/**
	 * Render a taxonomy row item.
	 *
	 * @param  \WP_Post $row The row data.
	 * @param  \EventManager\DataTables\Column $column The column this item belongs to.
	 * @param  string $taxonomy The taxonomy slug
	 */
	public function render_taxonomy_item( \WP_Post $row, \EventManager\DataTables\Column $column, $taxonomy ) {
		if ( ! is_a( $row, 'WP_Post' ) ) {
			$this->render_default_item( $row, $column );
		}

		$terms = get_the_terms( $row->ID, $taxonomy );

		if ( event_manager_core()->locations->location_taxonomy->get_name() === $taxonomy ) {
			// If we have "All" we don't need the rest - there may be a better way to do this?
			if ( has_term( 'All', $taxonomy, $row ) ) {
				unset( $terms );
				$terms[] = get_term_by( 'name', 'All', $taxonomy);
			}

			$this->render_location_item_terms( $terms );
		} else {
			$this->render_taxonomy_item_terms( $terms );
		}
	}

	/**
	 * Render the terms for a taxonomy row item.
	 *
	 * @param  array $terms The terms to render
	 */
	public function render_taxonomy_item_terms( $terms ) {
		$list = array();
		foreach ( (array) $terms as $term ) {
			if ( ! is_a( $term, 'WP_Term' ) ) {
				continue;
			}

			$list[] = '<span class="term">' . esc_html( $term->name ) . '</span>';
		}
		echo implode( ', ', $list );
	}

	/**
	 * Render the terms for a location row item.
	 *
	 * @param  array $terms The terms to render
	 */
	public function render_location_item_terms( $terms, $column = false ) {
		// Bail if we have no terms
		if ( empty( $terms ) ) {
			return;
		}

		// Get this out of the way: if there is only one, we don't need all the looping.
		if ( count( $terms ) === 1 ) {
			$term = $terms[0];
			echo \EventManager\Helpers\location_icon( $term->term_id );
			return;
		}

		$termsHierarchy = array();
		\EventManager\Helpers\sort_terms_hierarchically( $terms, $termsHierarchy );

		// I'm sorry, Dave. I'm afraid I can't do that.
		if ( empty( $termsHierarchy ) ) {
			return;
		}

		ob_start();

		?><div class="location-wrap"><?php

		foreach ( $termsHierarchy as $term ) {
			if ( ! is_a( $term, 'WP_Term' ) ) {
				continue;
			}

			?><div class="location-list -<?php echo esc_attr( strtolower( $term->slug ) ); ?>"><?php
			// echo Parent
			echo \EventManager\Helpers\location_icon( $term->term_id );

			if ( ! empty( $term->children ) ) {
				?><ul class="child-list"><?php
				foreach ( $term->children as $child ) {
					?><li class="child"><?php echo esc_html( $child->name ); ?></li><?php
				}
				?></ul><?php
			}
			?></div><?php
		}
		?></div><?php

		ob_get_flush();
	}

	/**
	 * Determine if the query for the table should use Elasticsearch.
	 *
	 * @return boolean
	 */
	public function should_use_elastic_search() {
		if ( ! isset( $this->query_args['ep_integrate'] ) ) {
			return true;
		}

		if (
			isset( $this->query_args['ep_integrate'] ) &&
			false === $this->query_args['ep_integrate']
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get the pagination links for the current query. This is useful for updating the links via AJAX.
	 *
	 * @return string The pagination links.
	 */
	public function get_pagination_links() {
		$query = $this->get_query();
		$big   = 999999999; // need an unlikely integer

		$page    = intval( $query->get( 'paged' ) );
		$current = $page > 1 ? $page : 1;

		$pagination = array(
			'base'     => esc_url( str_replace( $big, '%#%', get_pagenum_link( $big ) ) ),
			'current'  => intval( $current ),
			'mid_size' => 1,
			'total'    => intval( $query->max_num_pages ),
		);

		return paginate_links( $pagination );
	}

	/**
	 * Refine the results of the table.
	 *
	 * @param  string $results The current result data.
	 * @param  array $query_args The arguments to refine the query by.
	 * @return string The rendered rows.
	 */
	public function refine_results( $results, $query_args ) {
		$results = array();

		$args = $this->get_query_args();
		$args = array_merge( $args, $query_args );

		/**
		 * If `ep_integrate` is set `s` will be ignored, so we
		 * need to remove `ep_integrate`
		 *
		 * See: wp-content/plugins/elasticpress/classes/class-ep-api.php:1524
		 */
		if ( ! empty( $args['s'] ) ) {
			unset( $args['ep_integrate'] );
		}

		$args = apply_filters( 'event_manager_filter_refine_results_args', $args );

		$this->set_query_args( $args );
		$this->iterator = new WPQueryIterator( $this->get_query_args() );

		$results['header'] = $this->get_rendered_header();
		$results['rows'] = $this->get_rendered_rows();
		$results['footer'] = $this->get_rendered_footer();

		return $results;
	}

	/**
	 * Get the classes for a row.
	 *
	 * @param  \WP_Post $row The row data.
	 * @param  \EventManager\DataTables\Column $column The column this item belongs to.
	 * @return string
	 */
	public function get_row_classes( \WP_Post $row, \EventManager\DataTables\Column $column ) {
		if ( ! is_a( $row, 'WP_Post' ) ) {
			return;
		}


		$classes = esc_attr( $row->post_type );

		$filetype = \EventManager\Helpers\get_post_filetype( $row );
		if ( ! empty( $filetype ) ) {
			$classes .= ' ' . $filetype;
		}

		return $classes;
	}
}