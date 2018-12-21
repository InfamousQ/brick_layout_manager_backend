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
	 * Create new User for given $profile
	 * @param Profile $profile HybridAuth Profile that is used to set up the User
	 * @return int|bool Id of new User which is set to use given $email. False if generation failed.
	 */
	public function createUserForProfile(Profile $profile) {
		if (empty($profile->email) || empty($profile->displayName)) {
			return false;
		}

		$pdo = $this->db->getPDO();
		try {
			$pdo->beginTransaction();
			$stmt = $pdo->prepare('INSERT INTO public.user (email, name) VALUES (:email, :name) RETURNING id');
			$stmt->execute(array(':email' => $profile->email, ':name' => $profile->displayName));
			$new_user_id = $stmt->fetchColumn();
			$pdo->commit();
			return (int) $new_user_id;
		} catch (\PDOException $PDOException) {
			error_log($PDOException->getMessage());
			$pdo->rollBack();
			return false;
		}
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

	public function verifyUserId($user_id) {
		$pdo = $this->db->getPDO();
		$stmt = $pdo->prepare('SELECT id FROM public.user WHERE id = :user_id');
		$stmt->execute(array(':user_id' => $user_id));
		return $stmt->fetchColumn();
	}

	public function saveAccessTokenForUser($access_token, $provider_type_id, $user_id) {
		$pdo = $this->db->getPDO();
		try {
			$pdo->beginTransaction();
			$stmt = $pdo->prepare('INSERT INTO public.user_token (user_id, adapter_type, access_token, created_at, updated_at) VALUES (:user_id, :adapter_type, :access_token, now(), now()) ON CONFLICT (user_id, adapter_type) DO UPDATE SET access_token = :access_token, updated_at = now()');
			$stmt->execute([':user_id' => $user_id, ':adapter_type' => $provider_type_id, ':access_token' => $access_token]);
			$pdo->commit();
			return true;
		} catch (\PDOException $PDOException) {
			error_log($PDOException->getMessage());
			$pdo->rollBack();
			return false;
		}
	}
}