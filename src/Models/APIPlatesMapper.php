<?php

namespace InfamousQ\LManager\Models;

class APIPlatesMapper {

	public static function getJSON(Plate $plate) {
		return [
			'id'    => (int) $plate->id,
			'x'     => $plate->x,
			'y'     => $plate->y,
			'z'     => $plate->z,
			'h'     => $plate->h,
			'w'     => $plate->w,
			'color' => APIColorMapper::getJSON($plate->color),
		];
	}

	public static function getModulePlatesJSON(Module $module) {
		$result = [];
		/** @var Plate $plate */
		foreach ($module->plates as $plate) {
			$result[] = self::getJSON($plate);
		}
		return $result;
	}
}