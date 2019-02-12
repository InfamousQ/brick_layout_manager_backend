<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class User
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property string $name
 * @property string $email
 * @property-read \Spot\Collection $modules
 * @property-read \Spot\Collection $layouts
 */

class User extends \Spot\Entity {

	protected static $table = 'user';
	public static function fields() {
		return [
			'id'    => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'name'  => ['type' => 'string', 'required' => true],
			'email' => ['type' => 'string', 'required' => true],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'tokens'    => $mapper->hasMany($entity, UserToken::class, 'user_id'),
			'modules'   => $mapper->hasMany($entity, Module::class, 'user_id'),
			'layouts'   => $mapper->hasManyThrough($entity, Layout::class, LayoutModule::class, 'layout_id', 'id'),
		];
	}
}