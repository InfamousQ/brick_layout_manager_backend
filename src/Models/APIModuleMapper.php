<?php

namespace InfamousQ\LManager\Models;

class APIModuleMapper {
	public static function getJSON(Module $module) {
		/** @var User $author_user */
		$author_user = $module->user;
		return ['id' => (int) $module->id, 'name' => $module->name, 'href' => '/api/v1/modules/' . $module->id . '/', 'author' => APIUserMapper::getModuleAuthorSummary($author_user)];
	}

	public static function getUserModulesJSON(Module $module) {
		return ['id' => (int) $module->id, 'href' => '/api/v1/modules/' . $module->id . '/', 'name' => $module->name, 'created' => $module->created_at->format('U'), 'author' => APIUserMapper::getModuleAuthorSummary($module->user)];
	}

}