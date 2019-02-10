<?php

namespace InfamousQ\LManager\Models;

class LayoutModule extends \Spot\Entity {
	protected static $table = 'layout_module';
	public static function fields() {
		return [
			'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'layout_id' => ['type' => 'integer', 'required' => true],
			'module_id' => ['type' => 'integer', 'required' => true],
		];
	}
}