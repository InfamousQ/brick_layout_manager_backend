<?php

namespace InfamousQ\LManager\Models;

class APIModuleMapper {
	public static function getJSON(Module $module) {
		return ['id' => $module->id, 'name' => $module->name, 'href' => '/api/v1/modules/' . $module->id . '/', 'author' => []];
	}

}