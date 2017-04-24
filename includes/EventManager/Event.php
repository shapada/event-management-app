<?php

namespace EventManager;

use EventManager\Events\EventPostType;

class Event {

	public $event;

	public function __construct() {
		$this->event = new EventPostType();
	}

	public function register() {
		$this->event->register();
	}

	public function get_cpts() {
		return $this->event->get_name();
	}

}