<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 18.12.2018
 * Time: 23:03
 */

namespace InfamousQ\LManager\Services;

interface JWTServiceInterface {

	public function __construct();

	/**
	 * Generate JWT token for given $user_is
	 * @param int $user_id
	 * @return string Token
	 */
	public function generateToken($user_id);
}