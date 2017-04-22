<?php

namespace EventManager\DataTables;

/**
 * Abstract class for a data table.
 */
abstract class AbstractDataTable {

	/**
	 * The table columns.
	 *
	 * @var array
	 */
	protected $columns = array();

	/**
	 * The table rows.
	 *
	 * @var array
	 */
	protected $rows = array();

	/**
	 * The option to determine if the table is searchable.
	 *
	 * @var boolean
	 */
	protected $is_searchable = true;

	/**
	 * The option to determine if the table supports pagination.
	 *
	 * @var boolean
	 */
	protected $has_pagination = true;

	public function __construct() {}

	/**
	 * Register actions and filters.
	 */
	public function register() {

	}

	/**
	 * Set the option to determine if the table is searchable.
	 *
	 * @param boolean $value
	 */
	public function set_is_searchable( $value ) {
		$this->is_searchable = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get the option to determine if the table is searchable.
	 *
	 * @return boolean
	 */
	public function is_searchable() {
		return filter_var( $this->is_searchable, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Determine if pagination should be used on this table.
	 *
	 * @return boolean
	 */
	public function has_pagination() {
		return filter_var( $this->has_pagination, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get the table rows.
	 *
	 * @return array
	 */
	public function get_rows() {
		return $this->rows;
	}

	/**
	 * Set the table rows.
	 *
	 * @param array $rows
	 */
	public function set_rows( $rows ) {
		$this->rows = (array) $rows;
	}

	/**
	 * Get the table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Add a column to the table.
	 *
	 * @param \EventManager\DataTables\Column $column
	 */
	public function add_column( \EventManager\DataTables\Column $column ) {
		$this->columns[] = $column;
	}

	/**
	 * Renter the table.
	 */
	public function render() { ?>
        <div class="table-responsive">
            <table class="table table-striped jambo_table bulk_action">
                <thead>
                    <tr class="headings">
				        <?php $this->render_header(); ?>
				    </tr>
                <tbody>
                    <?php $this->render_rows(); ?>
                </tbody>
            </table>
		</div>
	<?php
	}

	/**
	 * Render the table's search field.
	 */
	public function render_search() {
		if ( $this->is_searchable() ) {
			get_template_part( 'template-parts/data-tables/search' );
		}
	}

	/**
	 * Render the table's header.
	 */
	public function render_header() { ?>
        <?php
            foreach( $this->get_columns() as $column ) {
            if ( ! is_a( $column, '\EventManager\DataTables\Column' ) ) {
                continue;
            } ?>

            <th class="<?php echo esc_attr( $column->get_id() ) ?>">
                <?php
                    switch ( $column->is_sortable() ) {
                        case true: ?>
                            <span><i class="fa fa-chevron-down"></i> <?php echo esc_html( $column->get_title() ) ?></span>
                            <?php
                            break;

                        default:
                            echo esc_html( $column->get_title() );
                            break;
                    }
                }
                ?>
            </th>
	<?php
	}

	/**
	 * Render the table's rows.
	 */
	public function render_rows() {
		foreach ( $this->get_rows() as $key => $row ) {
			$this->render_row( $row );
		}
	}

	/**
	 * Get the pre-rendered rows for the table. Useful for updating table results using AJAX.
	 *
	 * @return string The rendered rows.
	 */
	public function get_rendered_rows() {
		ob_start();
		$this->render_rows();
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Get the pre-rendered header for the table. Useful for updating table results using AJAX.
	 *
	 * @return string The rendered header.
	 */
	public function get_rendered_header() {
		ob_start();
		$this->render_header();
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Render a single row.
	 */
	public function render_row( $row ) { ?>
		<tr>
            <?php
                foreach( $this->get_columns() as $column ) {
                    if ( ! is_a( $column, '\EventManager\DataTables\Column' ) ) {
                        continue;
                    }

                    $this->render_row_item( $row, $column );
                } ?>
        </tr>
	<?php
	}

	/**
	 * Render a single row item.
	 *
	 * @param  array $row The row data.
	 * @param  \EventManager\DataTables\Column $column The column this row item belongs to.
	 */
	public function render_row_item( $row, Column $column ) {
		if ( ! is_a( $column, '\EventManager\DataTables\Column' ) ) {
			return;
		}

		$method = sprintf( 'render_%s_item', $column->get_id() );
		$signature = array( $this, $method );
		$method_args = array( $row, $column ); ?>

		<td class="<?php echo esc_attr( $column->get_id() ) ?>">
			<?php
			if ( method_exists( $this, $method ) &&
				 is_callable( $signature )
			) {
				call_user_func_array( $signature, $method_args );
			} else {
				$this->render_default_item( $row, $column );
			} ?>
		</td>
	<?php
	}
}
