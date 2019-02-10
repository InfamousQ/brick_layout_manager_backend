<?php

namespace InfamousQ\LManager\Models;

class Module {
	public $id;
	public $name;
	public $user_id;

	public function __construct($id = null, $name = null, $user_id = null) {
		$this->id = (int) $id;
		$this->name = $name;
		$this->user_id = (int) $user_id;
	}

	public function getData() {
		return ['id' => $this->id, 'name' => $this->name, 'href' => '/api/v1/modules/' . $this->id . '/', 'author' => []];
	}
}