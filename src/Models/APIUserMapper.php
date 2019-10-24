<?php

namespace InfamousQ\LManager\Models;

class APIUserMapper {
	public static function getJSON(User $user) {
		$user_modules_json = [];
		foreach ($user->modules as $module) {
			$user_modules_json[] = APIModuleMapper::getUserModulesJSON($module);
		}
		$user_layouts_json = [];
		foreach ($user->layouts as $layout) {
			$user_layouts_json[] = APILayoutMapper::getSummaryJSON($layout);
		}
		return ['id' => (int) $user->id, 'name' => $user->name, 'href' => '/api/v1/users/' . $user->id . '/', 'modules' => $user_modules_json, 'layouts' => $user_layouts_json];
	}

	public static function getModuleAuthorSummary($user) {
		return ['id' => (int) $user->id, 'name' => $user->name, 'href' => '/api/v1/users/' . $user->id . '/'];
	}

	public static function getLayoutAuthorSummary($user) {
		return ['id' => (int) $user->id, 'name' => $user->name, 'href' => '/api/v1/users/' . $user->id . '/'];
	}
}