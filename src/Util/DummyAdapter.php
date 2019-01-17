<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 18.12.2018
 * Time: 0:45
 */

namespace InfamousQ\LManager\Util;


use Hybridauth\Adapter\AbstractAdapter;
use Hybridauth\User\Profile;

class DummyAdapter extends AbstractAdapter {

	public function authenticate() {
		// TODO: Implement authenticate() method.
	}

	protected function configure() {
		// TODO: Implement configure() method.
	}

	protected function initialize() {
		// TODO: Implement initialize() method.
	}

	public function isConnected() {
		return true;
	}

	public function getUserProfile() {
		$dummy_profile = new Profile();
		$dummy_profile->displayName = 'Dummy P Person';
		$dummy_profile->email = 'dummy@lmanager.test';
		return $dummy_profile;
	}

	public function getAccessToken() {
		return [
			'access_token' => 'OAUTH_DUMMY_TOKEN',
		];
	}
}