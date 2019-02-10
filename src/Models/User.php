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
			'user'  => $mapper->belongsTo($entity, UserToken::class, 'user_id'),
		];
	}
}