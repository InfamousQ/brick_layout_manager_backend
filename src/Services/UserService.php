<?php

namespace InfamousQ\LManager\Services;


use Hybridauth\User\Profile;

class UserService implements UserServiceInterface {

	/** @var DatabaseServiceInterface $db */
	protected $db;

	public function __construct(DatabaseServiceInterface $db) {
		$this->db = $db;
	}

	/**
	 * Find id of existing User which has given $email
	 * @param string $email Email to search
	 * @return int|false Id of user if found, false if not found
	 */
	public function findUserIdByEmail($email) {
		$pdo = $this->db->getPDO();
		$stmt = $pdo->prepare('SELECT id FROM public.user WHERE email = :email');
		$stmt->execute(array(':email' => $email));
		return $stmt->fetchColumn();
	}

	/**
	 * Create new User for given $profile
	 * @param Profile $profile HybridAuth Profile that is used to set up the User
	 * @return int|bool Id of new User which is set to use given $email. False if generation failed.
	 */
	public function createUserForProfile(Profile $profile) {
		if (empty($profile->email) || empty($profile->displayName)) {
			return false;
		}

		$pdo = $this->db->getPDO();
		$pdo->beginTransaction();
		$stmt = $pdo->prepare('INSERT INTO public.user (email, name) VALUES (:email, :name) RETURNING id');
		$stmt->execute(array(':email' => $profile->email, ':name' => $profile->displayName));
		$new_user_id = $stmt->fetchColumn();
		if (!$pdo->commit()) {
			return false;
		}
		return (int) $new_user_id;

	}
}