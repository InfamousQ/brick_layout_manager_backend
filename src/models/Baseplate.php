<?php
namespace BLMRA\Model;

class Baseplate {
	public $id;

	protected $plates = array();

	public static function isValidContructor ($args) {
		if (!is_array($args)) {
			return false;
		}

		// Required fields exist
		$required_keys = array('id', 'plates');
		foreach ($required_keys as $k) {
			if (empty($args[$k])) {
				return false;
			}
		}

		// Check id
		if (!is_numeric($args['id'])) {
			return false;
		}

		$id = (int) $args['id'];
		if ($id < 0) {
			return false;
		}

		// Check plates
		$plates = $args['plates'];
		if (!is_array($plates)) {
			return false;
		}
		if (empty($plates)) {
			return false;
		}

		foreach ($plates as $plate) {
			if (!Plate::isValidContructor($plate)) {
				return false;
			}
		}

		return true;
	}
}