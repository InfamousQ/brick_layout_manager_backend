<?php

namespace InfamousQ\LManager\Services;


use Hybridauth\User\Profile;
use InfamousQ\LManager\Models\User;
use InfamousQ\LManager\Models\UserToken;

class UserService implements UserServiceInterface {

	/** @var \Spot\MapperInterface $mapper */
	protected $mapper;
	/** @var \Spot\MapperInterface $token_mapper */
	protected $token_mapper;

	public function __construct(MapperServiceInterface $mapper_service) {
		$this->mapper = $mapper_service->getMapper(User::class);
		$this->token_mapper = $mapper_service->getMapper(UserToken::class);
	}

	public function createUserForProfile(Profile $profile) {
		if (empty($profile->email) || empty($profile->displayName)) {
			return false;
		}

		/** @var User $entity */
		$entity = null;
		try {
			$entity = $this->mapper->create(['name' => $profile->displayName, 'email' => $profile->email]);
			return $entity;
		} catch (\Spot\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function createUserForUser(User $user) {
		$pdo = $this->db->getPDO();
		try {
			$pdo->beginTransaction();
			$stmt = $pdo->prepare('INSERT INTO public.user (email, name) VALUES (:email, :name) RETURNING id');
			$stmt->execute(array(':email' => $user->email, ':name' => $user->name));
			$new_user_id = $stmt->fetchColumn();
			$pdo->commit();
			return (int) $new_user_id;
		} catch (\PDOException $PDOException) {
			error_log($PDOException->getMessage());
			$pdo->rollBack();
			return false;
		}
	}

	public function getUserById($user_id) {
		$entity = $this->mapper->get($user_id);
		if ($entity === false) {
			return null;
		}
		return $entity;
	}

	public function saveUser(User $user) {
		try {
			$this->mapper->update($user);
			return true;
		} catch (\Spot\Exception $exception) {
			error_log($exception->getMessage());
			return false;
		}
	}

	public function findUserIdByEmail($email) {
		/** @var User $entity */
		$entity = $this->mapper->first(['email' => $email]);
		if (empty($entity->id)) {
			return null;
		}
		return (int) $entity->id;
	}

	public function saveAccessTokenForUser($access_token, $provider_type_id, $user_id) {
		/** @var UserToken $token_entity */
		$token_entity = null;
		try {
			$token_entity = $this->token_mapper->create(['user_id' => $user_id, 'adapter_type' => $provider_type_id, 'access_token' => $access_token]);
			return true;
		} catch (\Spot\Exception $exception) {
			error_log($exception->getMessage());
		}
		return false;
	}
}