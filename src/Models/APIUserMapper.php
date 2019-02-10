<?php

namespace InfamousQ\LManager\Models;

class APIUserMapper {
	public static function getJSON(User $user) {
		return ['id' => $user->id, 'name' => $user->name, 'href' => '/api/v1/users/' . $user->id . '/', 'modules' => [], 'layouts' => []];
	}
}