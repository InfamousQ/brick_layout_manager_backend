<?php

namespace InfamousQ\LManager\Services;


abstract class BaseAuthenticationService implements AuthenticationServiceInterface {

	const ADAPTER_TEST_PROVIDER = 1;
	const ADAPTER_FACEBOOK = 2;
	const ADAPTER_GITHUB = 3;

	/**
	 * Fetch provider type identifier for given $provider_name
	 * @param string $provider_name
	 * @return int
	 */
	public function getProviderType($provider_name) {
		$provider_name = strtolower($provider_name);
		$type = null;
		switch ($provider_name) {
			case 'facebook':
				$type = self::ADAPTER_FACEBOOK;
				break;
			case 'github';
				$type = self::ADAPTER_GITHUB;
				break;
			case 'test_provider';
				$type = self::ADAPTER_TEST_PROVIDER;
				break;
		}
		return $type;
	}
}