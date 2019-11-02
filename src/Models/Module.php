<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class Module
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property string $name
 * @property boolean $public
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 * @property-read User $user
 * @property-read \Spot\Entity\Collection plates
 * @property-read \Spot\Entity\Collection layouts
 */
class Module extends \Spot\Entity {
	protected static $table = 'module';
	public static function fields() {
		return [
			'id'            => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'user_id'       => ['type' => 'integer', 'required' => true],
			'name'          => ['type' => 'string', 'required' => true],
			'public'        => ['type' => 'boolean', 'default' => true],
			'created_at'    => ['type' => 'datetime', 'value' => new \DateTime()],
			'updated_at'    => ['type' => 'datetime', 'value' => new \DateTime()],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'user'      => $mapper->belongsTo($entity, User::class, 'user_id'),
			'plates'    => $mapper->hasMany($entity, Plate::class, 'module_id')->order(['id' => 'ASC']),
			'layouts'   => $mapper->hasManyThrough($entity, Layout::class, LayoutModule::class, 'layout_id', 'module_id')->order(['id' => 'ASC']),
		];
	}
}