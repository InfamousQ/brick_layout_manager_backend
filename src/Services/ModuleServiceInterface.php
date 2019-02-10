<?php

namespace InfamousQ\LManager\Services;


use InfamousQ\LManager\Models\Module;

interface ModuleServiceInterface {

	/**
	 * Generates a Module according to given data
	 * @param string $name Name of the module
	 * @param int $user_id Authoring user's id
	 * @return Module
	 */
	public function createModule($name, $user_id);

	/**
	 * Fetch given Module that matches given $module_id
	 * @param int $module_id
	 * @return Module
	 */
	public function getModuleById($module_id);

	/**
	 * Save given Module
	 * @param Module $module
	 * @return boolean Was save successful?
	 */
	public function saveModule(Module $module);
}