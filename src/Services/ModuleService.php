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

	public function deleteLayoutById($layout_id) {
		$layout_id = (int) $layout_id;
		return (bool) $this->layout_mapper->delete(['id' => $layout_id]);
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

	/**
	 * Generate Module with given $name and linked to given $user_id
	 * @param string $name
	 * @param int $user_id
	 * @return Module|bool Generated Module or false if creation had problems
	 */
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

	/**
	 * Fetch Module which id is $module_id. If not found, return null
	 * @param int $module_id Id of target Module
	 * @return Module|null Found targeted Module or null if not found
	 */
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

	/**
	 * Delete Module matching given $module_id from DB
	 * @param int $module_id
	 * @return bool Was deletion successful
	 */
	public function deleteModuleById($module_id) {
		$module_id = (int) $module_id;
		return (bool) $this->mapper->delete(['id' => $module_id]);
	}

	// Plate functions

	/**
	 * Generate Plate connected to given $module_id with given coordinates and given size
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @param int $h
	 * @param int $w
	 * @param int $color_id Id of Color that is used
	 * @param int $module_id Id of Module Plate is connected to
	 * @return Plate|false
	 */
	public function createPlate($x, $y, $z, $h, $w, $color_id, $module_id) {
		/** @var Plate $entity */
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

	/**
	 * Fetch Plate which id is given $plate_id. If not found, return null
	 * @param int $plate_id Target Plate's id
	 * @return Plate|null Target Plate or null if no Plate found
	 */
	public function getPlateById($plate_id) {
		$plate_id = (int) $plate_id;
		/** @var Plate $entity */
		$entity = $this->plate_mapper->get($plate_id);
		if ($entity === false) {
			return null;
		}
		return $entity;
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

	/**
	 * Generate Color with given $name and $hex
	 * @param string $name
	 * @param string $hex
	 * @return Color|false
	 */
	public function createColor($name, $hex) {
		/** @var Color $entity */
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