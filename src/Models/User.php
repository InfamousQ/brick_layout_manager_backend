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
}