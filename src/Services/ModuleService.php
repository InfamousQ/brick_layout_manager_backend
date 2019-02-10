<?php

namespace InfamousQ\LManager\Services;

use InfamousQ\LManager\Models\LayoutModule;
use \Spot\MapperInterface;
use InfamousQ\LManager\Models\Module;
use InfamousQ\LManager\Models\Color;
use InfamousQ\LManager\Models\Plate;
use InfamousQ\LManager\Models\Layout;

class ModuleService {

	/** @var MapperInterface $mapper */
	protected $mapper;
	/** @var MapperInterface $color_mapper */
	protected $color_mapper;
	/** @var MapperInterface $plate_mapper */
	protected $plate_mapper;
	/** @var MapperInterface $layout_mapper */
	protected $layout_mapper;
	/** @var MapperInterface$layout_module_mapper */
	protected $layout_module_mapper;

	public function __construct(MapperServiceInterface $mapper_service) {
		$this->mapper = $mapper_service->getMapper(Module::class);
		$this->color_mapper = $mapper_service->getMapper(Color::class);
		$this->plate_mapper = $mapper_service->getMapper(Plate::class);
		$this->layout_mapper = $mapper_service->getMapper(Layout::class);
		$this->layout_module_mapper = $mapper_service->getMapper(LayoutModule::class);
	}

	// Layout functions
	public function createLayout($name, $user_id) {
		/** @var Layout $entity */
		$entity = null;
		try {
			$entity = $this->layout_mapper->create(['name' => $name, 'user_id' => $user_id]);
			return $entity;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function getLayoutById($layout_id) {
		/** @var Layout $entity */
		$entity = $this->layout_mapper->get($layout_id);
		if (false === $entity) {
			return null;
		}
		return $entity;
	}

	public function connectModuleToLayout(Module $module, Layout $layout) {
		$matching_links = $this->layout_module_mapper->where(['layout_id' => $layout->id, 'module_id' => $module->id]);
		if ($matching_links->count() == 1) {
			// Connection found, return true
			return true;
		}
		// Connection not found, create connection
		try {
			$this->layout_module_mapper->create(['layout_id' => $layout->id, 'module_id' => $module->id]);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	// Module functions

	public function createModule($name, $user_id) {
		/** @var Module $entity */
		$entity = null;
		try {
			$entity = $this->mapper->create(['name' => $name, 'user_id' => $user_id]);
			return $entity;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function getModuleById($module_id) {
		/** @var Module $entity */
		$entity = $this->mapper->get($module_id);
		if ($entity === false) {
			return null;
		}
		return $entity;
	}

	public function saveModule(Module $module) {
		try {
			$this->mapper->update($module);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	// Plate functions

	public function createPlate($x, $y, $z, $h, $w, $color_id, $module_id) {
		$entity = null;
		try {
			$entity = $this->plate_mapper->create([
				'x' => $x,
				'y' => $y,
				'z' => $z,
				'h' => $h,
				'w' => $w,
				'color_id' => $color_id,
				'module_id' => $module_id,
			]);
			return $entity;
		} catch (\Spot\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function savePlate(Plate $plate) {
		try {
			$this->plate_mapper->update($plate);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	// Color functions

	public function createColor($name, $hex) {
		$entity = null;
		try {
			$entity = $this->color_mapper->create(['name' => $name, 'hex' => $hex]);
			return $entity;
		} catch (\Spot\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function saveColor(Color $color) {
		try {
			$this->color_mapper->update($color);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}
}