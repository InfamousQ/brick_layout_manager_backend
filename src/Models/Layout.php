<?php

namespace InfamousQ\LManager\Models;

use \DateTime;
use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class Layout
 * @package InfamousQ\LManager\Models
 * @property-read int $id
 * @property string $name
 * @property bool $public
 * @property int $w Width in studs
 * @property int $h Height in studs
 * @property-read DateTime $created_at
 * @property-read DateTime $updated_at
 * @property-read User $user
 * @property-read \Spot\Entity\Collection $modules
 */
class Layout extends \Spot\Entity {
	protected static $table = 'layout';
	public static function fields() {
		return [
			'id'            => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'name'          => ['type' => 'string', 'required' => true],
			'w'             => ['type' => 'integer', 'required' => true],
			'h'             => ['type' => 'integer', 'required' => true],
			'user_id'       => ['type' => 'integer', 'required' => true],
			'public'        => ['type' => 'boolean', 'default' => false],
			'created_at'    => ['type' => 'datetime', 'value' => new DateTime()],
			'updated_at'    => ['type' => 'datetime', 'value' => new DateTime()],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'user'      => $mapper->belongsTo($entity, User::class, 'user_id'),
			'modules'   => $mapper->hasMany($entity, LayoutModule::class,  'layout_id'),
		];
	}
}