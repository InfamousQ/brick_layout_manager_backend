<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class Module
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property string $name
 * @property-read User $user
 * @property-read \Spot\Entity\Collection plates
 */
class Module extends \Spot\Entity {
	protected static $table = 'module';
	public static function fields() {
		return [
			'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'user_id'   => ['type' => 'integer', 'required' => true],
			'name'      => ['type' => 'string', 'required' => true],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'user'      => $mapper->belongsTo($entity, User::class, 'user_id'),
			'plates'    => $mapper->hasMany($entity, Plate::class, 'module_id'),
		];
	}
}