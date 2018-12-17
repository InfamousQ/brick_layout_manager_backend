<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 15.12.2018
 * Time: 22:18
 */

namespace InfamousQ\LManager\Services;


use Hybridauth\User\Profile;

class UserService {

	/**
	 * Find id of existing User which has given $email
	 * @param string $email Email to search
	 * @return int|false Id of user if found, false if not found
	 */
	public static function findUserIdByEmail($email) {
		$db = DB::getInstance();
		$stmt = $db
			->select(['id'])
			->from('public.user')
			->where('email', '=', $email)
			->execute();
		return $stmt->fetchColumn();
	}

	/**
	 * Create new User for given $profile
	 * @param Profile $profile HybridAuth Profile that is used to set up the User
	 * @return int|bool Id of new User which is set to use given $email. False if generation failed.
	 */
	public static function createUserForProfile(Profile $profile) {
		if (empty($profile->email) || empty($profile->displayName)) {
			return false;
		}
		$db = DB::getInstance();
		$db->beginTransaction();
		$new_user_id = $db
			->insert(['email', 'name'])
			->into('public.user')
			->values([$profile->email, $profile->displayName])
			->execute();
		if (!$db->commit()) {
			return false;
		}
		return (int) $new_user_id;

	}
}