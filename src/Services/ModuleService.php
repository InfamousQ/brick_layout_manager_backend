<?php

namespace InfamousQ\LManager\Services;

use InfamousQ\LManager\Models\LayoutModule;
use Spot\Entity\Collection;
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
	/** @var MapperInterface $layout_module_mapper */
	protected $layout_module_mapper;

	public function __construct(MapperServiceInterface $mapper_service) {
		$this->mapper = $mapper_service->getMapper(Module::class);
		$this->color_mapper = $mapper_service->getMapper(Color::class);
		$this->plate_mapper = $mapper_service->getMapper(Plate::class);
		$this->layout_mapper = $mapper_service->getMapper(Layout::class);
		$this->layout_module_mapper = $mapper_service->getMapper(LayoutModule::class);
	}

	// Layout functions

	/**
	 * Create new Layout based on given $name and $user_id
	 * @param string $name Name for the layout
	 * @param int $user_id Id of author User
	 * @param int $width Width of the layout in studs
	 * @param int $height Height
	 * @return bool|Layout Created Layout or false
	 */
	public function createLayout($name, $user_id, $width = 32, $height = 32) {
		/** @var Layout $entity */
		$entity = null;
		try {
			$entity = $this->layout_mapper->create(['name' => $name, 'user_id' => $user_id, 'w' => $width, 'h' => $height]);
			return $entity;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	/**
	 * Save given $layout to DB
	 * @param Layout $layout
	 * @return bool Was data saved?
	 */
	public function saveLayout(Layout $layout) {
		try {
			$this->layout_mapper->save($layout);
			return true;
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

	/**
	 * Get all public Layouts
	 * @return Collection Layout
	 */
	public function getPublicLayouts() {
		/** @var Collection $public_layouts */
		$public_layouts = $this->layout_mapper->select()->where(['public' => true])->order(['updated_at' => 'DESC'])->execute();
		return $public_layouts;
	}

	public function getLayouts(int $filter_by_user_id = null) {
		$layout_query = $this->layout_mapper->select();
		if (null !== $filter_by_user_id) {
			$layout_query->where(['user_id' => (int) $filter_by_user_id]);
		}
		$layout_query->order(['updated_at' => 'DESC']);
		return $layout_query->execute();
	}

	public function updateLayout(Layout $layout) {
		$this->layout_mapper->update($layout, ['relations' => true,]);
	}

	public function connectModuleToLayout(Layout $layout, Module $module, $x, $y) {
		$matching_links = $this->layout_module_mapper->where(['layout_id' => $layout->id, 'module_id' => $module->id]);
		if ($matching_links->count() == 1) {
			// Connection found, return true
			return true;
		}
		// Connection not found, create connection
		try {
			$this->layout_module_mapper->create(['layout_id' => $layout->id, 'module_id' => $module->id, 'x' => $x, 'y' => $y]);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function saveModuleInLayout(LayoutModule $layout_module) {
		try {
			$this->layout_module_mapper->save($layout_module);
			return true;
		} catch (\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function deleteModuleInLayout(LayoutModule $layout_module) {
		try {
			$this->layout_module_mapper->delete(['id' => (int) $layout_module->id]);
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
	 * @param int $width
	 * @param int $height
	 * @return Module|bool Generated Module or false if creation had problems
	 */
	public function createModule($name, $user_id, $width = 32, $height = 32) {
		/** @var Module $entity */
		$entity = null;
		try {
			$entity = $this->mapper->create(['name' => $name, 'user_id' => $user_id, 'w' => $width, 'h' => $height]);
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

	/**
	 * Fetch all Modules that are set public
	 * @return Collection
	 */
	public function getPublicModules() {
		/** @var Collection $public_modules */
		$public_modules = $this->mapper->select()->where(['public' => true])->order(['updated_at' => 'DESC'])->execute();
		return $public_modules;
	}

	/**
	 *
	 * @param int $user_id
	 * @return Collection
	 */
	public function getModules($user_id) {
		/** @var Collection $public_modules */
		$public_modules = $this->mapper->select()->where(['user_id' => (int) $user_id])->order(['updated_at' => 'DESC'])->execute();
		return $public_modules;
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

	/**
	 * Delete Plate matching given $plate_id
	 * @param int $plate_id Id of target Plate
	 * @return bool Was deletion successful?
	 */
	public function deletePlateById($plate_id) {
		$plate_id = (int) $plate_id;
		return (bool) $this->plate_mapper->delete(['id' => $plate_id]);
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

	/**
	 * @return \Spot\Query
	 */
	public function getColors() {
		return $this->color_mapper->all()->order(['id' => 'ASC']);
	}
}