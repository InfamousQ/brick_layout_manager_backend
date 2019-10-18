<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class Plate
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property int $x
 * @property int $y
 * @property int $z
 * @property int $h
 * @property int $w
 * @property-read Color $color
 * @property-read Module $module
 */
class Plate extends \Spot\Entity {
	protected static $table = 'plate';
	public static function fields() {
		return [
			'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'module_id' => ['type' => 'integer', 'required' => true],
			'x'         => ['type' => 'integer', 'required' => true],
			'y'         => ['type' => 'integer', 'required' => true],
			'z'         => ['type' => 'integer', 'required' => true],
			'h'         => ['type' => 'integer', 'required' => true],
			'w'         => ['type' => 'integer', 'required' => true],
			'color_id'  => ['type' => 'integer', 'required' => true],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'module'    => $mapper->belongsTo($entity, Module::class, 'module_id'),
			'color'     => $mapper->belongsTo($entity, Color::class, 'color_id'),
		];
	}
}