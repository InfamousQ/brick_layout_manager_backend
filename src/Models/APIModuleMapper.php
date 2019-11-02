<?php

namespace InfamousQ\LManager\Models;

class APIModuleMapper {
	/**
	 * @param Module $module
	 * @return array
	 */
	public static function getJSON($module) {
		/** @var User $author_user */
		$author_user = $module->user;
		return [
			'id' => (int) $module->id,
			'href' => '/api/v1/modules/' . $module->id . '/',
			'name' => $module->name,
			'public' => (bool) $module->public,
			'author' => APIUserMapper::getModuleAuthorSummary($author_user),
			'created' => $module->created_at->format(\DateTimeInterface::RFC3339),
			'plates' => APIPlatesMapper::getModulePlatesJSON($module),
			];
	}

	public static function getSummaryJSON($module) {
		/** @var User $author_user */
		$author_user = $module->user;
		return [
			'id' => (int) $module->id,
			'href' => '/api/v1/modules/' . $module->id . '/',
			'name' => $module->name,
			'author' => APIUserMapper::getModuleAuthorSummary($author_user),
			'created' => $module->created_at->format(\DateTimeInterface::RFC3339),
		];
	}

	public static function getUserModulesJSON($module) {
		return [
			'id' => (int) $module->id,
			'href' => '/api/v1/modules/' . $module->id . '/',
			'name' => $module->name,
			'public' => (bool) $module->public,
			'author' => APIUserMapper::getModuleAuthorSummary($module->user),
			'created' => $module->created_at->format(\DateTimeInterface::RFC3339),
			];
	}
}