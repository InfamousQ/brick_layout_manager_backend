<?php

namespace InfamousQ\LManager\Middleware;


use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;
use InfamousQ\LManager\Util\DummyAdapter;
use InfamousQ\LManager\Util\RuntimeHybridauthStorage;

class DummyAuthService {

	public static $callback = '';
	public static $provider_config = array();

	public function __construct($config) {
		self::$callback = $config['callback'];
		self::$provider_config = $config['providers'];
	}

	/**
	 * Fetch configuration for given provider $provider_name
	 * @param string $provider_name
	 * @return array
	 */
	public function getProviderConfig($provider_name) {
		if (!is_array(self::$provider_config)) {
			return array();
		}
		if (array_key_exists($provider_name, self::$provider_config)) {
			$provider_config = self::$provider_config[$provider_name];
			if (empty($provider_config['enabled'] || empty($provider_config['keys']))) {
				throw new UnexpectedValueException("Provider '$provider_name' is not configured'");
			}
			return $provider_config;
		}
		throw new InvalidArgumentException("Provider '$provider_name' not found'");
	}

	public function authenticate() {
		return new DummyAdapter(array(), null, new RuntimeHybridauthStorage());
	}
}