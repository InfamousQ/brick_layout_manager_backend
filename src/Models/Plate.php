<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

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
			'user'      => $mapper->belongsTo($entity, User::class, 'user_id'),
			'color'     => $mapper->belongsTo($entity, Color::class, 'color_id'),
		];
	}
}