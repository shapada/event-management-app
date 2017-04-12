<?php

namespace EventManager;

use EventManager\Taxonomies\VenueLocationTaxonomy;
use EventManager\Taxonomies\VenueTypeTaxonomy;
use EventManager\Venue\VenuePostType;

class Venue {

	public $venue;
	public $venue_type_taxonomy;
	public $venue_location_taxonomy;

	public function __construct() {
		$this->venue = new VenuePostType();
		$this->venue_type_taxonomy = new VenueTypeTaxonomy();
		$this->venue_location_taxonomy = new VenueLocationTaxonomy();
	}

	public function register() {
		$this->venue->register();
		$this->venue_type_taxonomy->register();
		$this->venue_location_taxonomy->register();
	}

	public function get_cpts() {
		return $this->venue->get_name();
	}

}