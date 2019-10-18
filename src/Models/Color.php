<?php

namespace InfamousQ\LManager\Models;

/**
 * Class Color
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property string $name
 * @property string $hex
 */
class Color extends \Spot\Entity {
	protected static $table = 'color';
	public static function fields() {
		return [
			'id'    => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'name'  => ['type' => 'string', 'required' => true],
			'hex'   => ['type' => 'string', 'required' => true],
		];
	}
}