<?php

namespace InfamousQ\LManager\Models;

class APIModuleMapper {
	public static function getJSON(Module $module) {
		/** @var User $author_user */
		$author_user = $module->user;
		return [
			'id' => (int) $module->id,
			'href' => '/api/v1/modules/' . $module->id . '/',
			'name' => $module->name,
			'public' => (bool) $module->public,
			'author' => APIUserMapper::getModuleAuthorSummary($author_user),
			'created' => $module->created_at->format(\DateTimeInterface::RFC3339),
			];
	}

	public static function getSummaryJSON(Module $module) {
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

	public static function getUserModulesJSON(Module $module) {
		return [
			'id' => (int) $module->id,
			'href' => '/api/v1/modules/' . $module->id . '/',
			'name' => $module->name,
			'public' => (bool) $module->public,
			'author' => APIUserMapper::getModuleAuthorSummary($module->user),
			'created' => $module->created_at->format(\DateTimeInterface::RFC3339),
			];
	}

	public static function getLayoutModulesJSON(Module $module) {
		return [
			'id' => (int) $module->id,
			'href' => '/api/v1/modules/' . $module->id . '/',
			'name' => $module->name,
			'public' => (bool) $module->public,
			'created' => $module->created_at->format(\DateTimeInterface::RFC3339),
		];
	}

}