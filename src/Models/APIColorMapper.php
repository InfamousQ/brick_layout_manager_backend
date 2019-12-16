<?php

namespace InfamousQ\LManager\Models;

class APIColorMapper {

	public static function getJSON($color) {
		/** @var Color $color */
		return ['id' => (int) $color->id, 'name' => $color->name, 'hex' => $color->hex];
	}

}