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