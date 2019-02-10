<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class UserToken
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property int adapter_type
 * @property string $access_token
 */

class UserToken extends \Spot\Entity {
	protected static $table = 'user_token';
	public static function fields() {
		return [
			'id'            => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
			'user_id'       => ['type' => 'integer', 'required' => true, 'unique' => 'user_adapter'],
			'adapter_type'  => ['type' => 'integer', 'required' => true, 'unique' => 'user_adapter'],
			'access_token'  => ['type' => 'string', 'required' => true],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'user'  => $mapper->belongsTo($entity, User::class, 'user_id'),
		];
	}

}