<?php

namespace InfamousQ\LManager\Services;

use Hybridauth\Hybridauth;
use Hybridauth\Storage\Session;


class HybridAuthService extends BaseAuthenticationService {

	/** @var Hybridauth $hybridauth instance*/
	protected $hybridauth;
	/** @var Session $session Hybridauth session */
	protected $session;
	/** @var array Provider's info */
	protected static $provider_config = array();

	const PROVIDER_NAME_KEY = 'provider_key';

	public function __construct(array $config) {
		$authention_service_config = $config;
		self::$provider_config = $authention_service_config['providers'];
		$this->session = new Session();
		$this->hybridauth = new Hybridauth($authention_service_config, null, $this->session);
	}

	public function getProviderConfig($provider_name) {
		return $this->hybridauth->getProviderConfig($provider_name);
	}

	public function getAvailableProviders() {
		$provider_data = [];
		foreach (self::$provider_config as $provider_config) {
			$provider_data[] = [
				'name' => $provider_config['name'],
				'code' => $provider_config['code'],
				'icon' => $provider_config['icon'],
			];
		}
		return $provider_data;
	}

	public function getConnectedProviders() {
		return $this->hybridauth->getConnectedProviders();
	}

	public function authenticate($provider_name) {
		return $this->hybridauth->authenticate($provider_name);
	}

	public function disconnectAllAdapters() {
		$this->hybridauth->disconnectAllAdapters();
		return true;
	}

	public function getAdapter($provider_name) {
		return $this->hybridauth->getAdapter($provider_name);
	}

	public function setProviderToStorage($provider_name) {
		$this->session->set(self::PROVIDER_NAME_KEY, $provider_name);
	}

	public function getProviderFromStorage() {
		return $this->session->get(self::PROVIDER_NAME_KEY);
	}
}