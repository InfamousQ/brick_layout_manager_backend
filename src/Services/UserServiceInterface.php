<?php

namespace InfamousQ\LManager\Services;

interface UserServiceInterface {

	public function __construct(DatabaseServiceInterface $db);

	public function createUserForProfile(\Hybridauth\User\Profile $profile);

	/**
	 * Find existing user's id by given $email
	 * @param string $email Email to search for
	 * @return int|false Found user id or false if nothing found
	 */
	public function findUserIdByEmail($email);

	/**
	 * Get existing User for given $user_id
	 * @param int $user_id
	 * @return \InfamousQ\LManager\Models\User User data
	 */
	public function getUserById($user_id);

	/**
	 * Save given User to database
	 * @param \InfamousQ\LManager\Models\User $user
	 * @return boolean Was save successful
	 */
	public function saveUser(\InfamousQ\LManager\Models\User $user);

	/**
	 * Verify that given user id exists
	 * @param int $user_id User id to check
	 * @return boolean Does given $user_id exist?
	 */
	public function verifyUserId($user_id);

	/**
	 * @param string $access_token Access token from social login provider
	 * @param int $provider_type_id
	 * @param int $user_id
	 * @return boolean Was save successful?
	 */
	public function saveAccessTokenForUser($access_token, $provider_type_id, $user_id);
}