<?php

namespace InfamousQ\LManager\Models;

class APILayoutMapper {
	public static function getJSON(Layout $layout) {
		/** User $author_user */
		$author_user = $layout->user;
		$modules = [];
		foreach ($layout->modules as $module) {
			$modules[] = APILayoutModuleMapper::getSummaryJSON($module);
		}
		return [
			'id' => (int) $layout->id,
			'href' => '/api/v1/layouts/' . $layout->id .'/',
			'name' => $layout->name,
			'public' => (bool) $layout->public,
			'author' => APIUserMapper::getLayoutAuthorSummary($author_user),
			'created' => $layout->created_at->format(\DateTimeInterface::RFC3339),
			'modules' => $modules,
		];
	}

	public static function getSummaryJSON(Layout $layout) {
		/** User $author_user */
		$author_user = $layout->user;
		$modules = [];
		foreach ($layout->modules as $module) {
			$modules[] = APILayoutModuleMapper::getSummaryJSON($module);
		}
		return [
			'id' => (int) $layout->id,
			'href' => '/api/v1/layouts/' . $layout->id .'/',
			'name' => $layout->name,
			'public' => (bool) $layout->public,
			'author' => APIUserMapper::getLayoutAuthorSummary($author_user),
			'created' => $layout->created_at->format(\DateTimeInterface::RFC3339),
			'modules' => $modules,
		];
	}
}