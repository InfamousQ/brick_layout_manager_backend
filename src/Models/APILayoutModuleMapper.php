<?php

namespace InfamousQ\LManager\Models;

class APILayoutModuleMapper {
	/**
	 * @param LayoutModule $layout_module
	 * @return array
	 */
	public static function getSummaryJSON($layout_module) {
		/** @var Module $target_module */
		$target_module = $layout_module->module;
		return [
			'id' => (int) $layout_module->id,
			'href' => "/api/v1/modules/{$target_module->id}/",
			'name' => $target_module->name,
			'author' => APIUserMapper::getModuleAuthorSummary($target_module->user),
			'x' => (int) $layout_module->x,
			'y' => (int) $layout_module->y,
			'created' => $target_module->created_at->format(\DateTimeInterface::RFC3339),
		];
	}
}