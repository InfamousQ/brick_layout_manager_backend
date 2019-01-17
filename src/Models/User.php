<?php

namespace InfamousQ\LManager\Models;


class User {
	public $id;
	public $name;
	public $email;

	public function __construct($id = null, $email = null, $name = null) {
		$this->id = $id;
		$this->email = $email;
		$this->name = $name;
	}

	public function getData() {
		return ['id' => $this->id, 'name' => $this->name, 'href' => '/api/v1/users/' . $this->id . '/', 'modules' => [], 'layouts' => []];
	}
}