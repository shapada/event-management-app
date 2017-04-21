<?php

namespace EventManager;

use EventManager\Entertainers\EntertainerPostType;

use EventManager\Taxonomies\EntertainerTypeTaxonomy;

class Entertainer {

	public $entertainer;

	public $entertainer_type_taxonomy;

	public function __construct() {
		$this->entertainer = new EntertainerPostType();
		$this->entertainer_type_taxonomy = new EntertainerTypeTaxonomy();
	}

	public function register() {
		$this->entertainer->register();
		$this->entertainer_type_taxonomy->register();
	}

	public function get_cpts() {
		return $this->entertainer->get_name();
	}

}