<?php

namespace EventManager\DataTables;

/**
 * Class to represent a column in a data table.
 */
class Column {

	/**
	 * The row title.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * The row ID. This should be a slug to uniquely identify a column in a table.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * The option to determine if the column is sortable.
	 *
	 * @var boolean
	 */
	protected $is_sortable = false;

	public function __construct( $title, $id, $is_sortable = false ) {
		$this->set_title( $title );
		$this->set_id( $id );
		$this->set_is_sortable( $is_sortable );
	}

	/**
	 * Set the column title.
	 *
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Get the column's title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set the column's ID.
	 *
	 * @param string $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get the columns ID.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set the option to determine if a column is sortable.
	 *
	 * @param boolean $value
	 */
	public function set_is_sortable( $value ) {
		$this->is_sortable = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Get the option to determine if a column is sortable.
	 *
	 * @param boolean $value
	 */
	public function is_sortable() {
		return filter_var( $this->is_sortable, FILTER_VALIDATE_BOOLEAN );
	}
}
