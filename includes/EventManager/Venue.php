<?php

namespace EventManager;

use EventManager\Venue\VenuePostType;

class Venue {

	public $venue;

	public function __construct() {
		$this->venue = new VenuePostType();
	}

	public function register() {
		$this->venue->register();
	}

}