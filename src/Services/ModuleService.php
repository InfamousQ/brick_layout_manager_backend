<?php

namespace InfamousQ\LManager\Services;

use InfamousQ\LManager\Models\Module;

class ModuleService implements ModuleServiceInterface {

	/** @var DatabaseServiceInterface $db */
	protected $db;

	public function __construct(DatabaseServiceInterface $db) {
		$this->db = $db;
	}

	public function createModule($name, $user_id) {
		$name = trim($name);
		$user_id = (int)$user_id;
		$pdo = $this->db->getPDO();

		try {
			$pdo->beginTransaction();
			$stmt = $pdo->prepare('INSERT INTO public.module(name, user_id) VALUES (:name, :user_id) RETURNING id');
			$stmt->execute([':name' => $name, ':user_id' => $user_id]);
			$new_module_id = $stmt->fetchColumn();
			$pdo->commit();
			return new Module($new_module_id, $name, $user_id);
		} catch (\PDOException $PDOException) {
			error_log($PDOException->getMessage());
			$pdo->rollBack();
			return null;
		}
	}

	public function getModuleById($module_id) {
		$module_id = (int)$module_id;
		$pdo = $this->db->getPDO();
		$stmt = $pdo->prepare('SELECT id, name, user_id FROM public.module WHERE id = :module_id');
		$stmt->execute(array(':module_id' => $module_id));
		$db_module_data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		if (count($db_module_data) !== 1) {
			return null;
		}
		$module_data = reset($db_module_data);
		return new Module(
			$module_data['id'],
			$module_data['name'],
			$module_data['user_id']
		);
	}

	public function saveModule(Module $module) {
		$pdo = $this->db->getPDO();
		try {
			$pdo->beginTransaction();
			$stmt = $pdo->prepare('UPDATE public.module SET name = :name, user_id = :user_id WHERE id = :module_id');
			$stmt->execute([':name' => $module->name, ':module_id' => $module->id, ':user_id' => $module->user_id]);
			return $pdo->commit();
		} catch (\PDOException $PDOException) {
			error_log($PDOException->getMessage());
			$pdo->rollBack();
			return false;
		}
	}
}