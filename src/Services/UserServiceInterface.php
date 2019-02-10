<?php

namespace InfamousQ\LManager\Services;

use \InfamousQ\LManager\Models\User;

interface UserServiceInterface {

	public function __construct(MapperServiceInterface $db);

	/**
	 * Create new User for given $profile
	 * @param \Hybridauth\User\Profile $profile HybridAuth Profile that is used to set up the User
	 * @return User|bool New User which is set to use given $email. False if generation failed.
	 */
	public function createUserForProfile(\Hybridauth\User\Profile $profile);

	/**
	 * Find id of existing User which has given $email
	 * @param string $email Email to search
	 * @return int|null Id of user if found, null if not found
	 */
	public function findUserIdByEmail($email);

	/**
	 * Get existing User for given $user_id
	 * @param int $user_id
	 * @return User User data
	 */
	public function getUserById($user_id);

	/**
	 * Save given User to database
	 * @param User $user
	 * @return boolean Was save successful
	 */
	public function saveUser(User $user);

	/**
	 * @param string $access_token Access token from social login provider
	 * @param int $provider_type_id
	 * @param int $user_id
	 * @return boolean Was save successful?
	 */
	public function saveAccessTokenForUser($access_token, $provider_type_id, $user_id);
}