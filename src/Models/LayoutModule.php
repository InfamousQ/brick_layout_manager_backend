<?php

namespace InfamousQ\LManager\Models;

use Spot\EntityInterface;
use Spot\MapperInterface;

/**
 * Class LayoutModule
 * @property-read int $id
 * @property int $x
 * @property int $y
 * @property-read Module $module
 * @property-read Layout $layout
 * @package InfamousQ\LManager\Models
 */

class LayoutModule extends \Spot\Entity {
	protected static $table = 'layout_module';
	public static function fields() {
		return [
			'id'        => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
			'layout_id' => ['type' => 'integer', 'required' => true],
			'module_id' => ['type' => 'integer', 'required' => true],
			'x'         => ['type' => 'integer', 'required' => true],
			'y'         => ['type' => 'integer', 'required' => true],
		];
	}

	public static function relations(MapperInterface $mapper, EntityInterface $entity) {
		return [
			'module'    => $mapper->belongsTo($entity, Module::class, 'module_id'),
			'layout'     => $mapper->belongsTo($entity, Layout::class, 'layout_id'),
		];
	}
}