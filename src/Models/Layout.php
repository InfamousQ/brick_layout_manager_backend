<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class Layout
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property string $name
 * @property-read User $user
 * @property-read \Spot\Entity\Collection $modules
 */
class Layout extends \Spot\Entity {
	protected static $table = 'layout';
	public static function fields() {
		return [
			'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'name'      => ['type' => 'string', 'required' => true],
			'user_id'   => ['type' => 'integer', 'required' => true],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'user'      => $mapper->belongsTo($entity, User::class, 'user_id'),
			'modules'   => $mapper->hasManyThrough($entity, Module::class, LayoutModule::class, 'module_id', 'layout_id'),
		];
	}
}