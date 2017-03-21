<?php

namespace EventManager\DataTables;

use EventManager\Utils\Iterators\WPUserQueryIterator;
use EventManager\TemplateTags;

/**
 * Abstract class for a WP_USer_Query-powered data table.
 */
abstract class AbstractWPUserQueryDataTable extends AbstractDataTable {

	protected $iterator;
	protected $query_args = array();

	public $num_per_page = 10;

	public function __construct( $query_args = array() ) {
		$this->query_args   = $query_args;
		$this->num_per_page = get_option( 'posts_per_page' );
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
			$this->iterator = new WPUserQueryIterator( $this->get_query_args() );
		}

		return $this->iterator->get_items();
	}

	/**
	 * Set the query args for the table.
	 *
	 * @param array $query_args
	 */
	public function set_query( $query_args ) {
		$this->iterator = new WPUserQueryIterator( $query_args );
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

	public function get_default_query_args() {
		$page   = max( 1, filter_input( INPUT_GET, 'page' ) );
		$search = filter_input( INPUT_GET, 'search_query' );

		$args = array(
			'number'  => absint( $this->num_per_page ),
			'paged'   => absint( $page ),
			'orderby' => 'display_name',
		);

		if ( ! empty( $search ) ) {
			$args['search'] = '*' . $search . '*';
			$args['search_columns'] = array(
				'user_login',
				'user_email',
			);
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
		return is_a( $this->iterator, 'EventManager\Utils\Iterators\WPUserQueryIterator' );
	}

	/**
	 * Render the table's rows.
	 */
	public function render_rows() {
		if ( ! $this->has_iterator() ) {
			$this->iterator = new WPUserQueryIterator( $this->get_query_args() );
		}

		$this->iterator->loop( array( $this, 'render_row' ) );
	}

	/**
	 * Render the title row item.
	 *
	 * @param  \WP_User $row The row data.
	 * @param  \EventManager\DataTables\Column $column The column this item belongs to.
	 */
	public function render_title_item( \WP_User $row, \EventManager\DataTables\Column $column ) {
		if ( ! is_a( $row, 'WP_User' ) ) {
			$this->render_default_item( $row, $column );
		} ?>

		<a href="<?php echo esc_url( get_author_posts_url( $row->ID ) ) ?>">
			<?php echo esc_html( $row->display_name ); ?>
		</a>

	<?php
	}

	/**
	 * Get the pagination links for the current query. This is useful for updating the links via AJAX.
	 *
	 * @return string The pagination links.
	 */
	public function get_pagination_links() {
		$query = $this->get_query();
		$big   = 999999999; // need an unlikely integer

		$page        = absint( $query->get( 'paged' ) );
		$current     = $page > 1 ? $page : 1;
		$total_pages = absint( ceil( $query->get_total() ) / absint( $this->num_per_page ) );

		$pagination = array(
			'base'     => esc_url( str_replace( $big, '%#%', get_pagenum_link( $big ) ) ),
			'current'  => absint( $current ),
			'mid_size' => 1,
			'total'    => absint( $total_pages ),
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

		$this->set_query_args( $args );
		$this->iterator = new WPUserQueryIterator( $this->get_query_args() );

		$results['rows'] = $this->get_rendered_rows();
		$results['footer'] = $this->get_rendered_footer();

		return $results;
	}
}
