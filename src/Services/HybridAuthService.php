<?php

namespace InfamousQ\LManager\Services;

use Hybridauth\Hybridauth;
use Hybridauth\Storage\Session;


class HybridAuthService extends BaseAuthenticationService {

	/** @var $hybridauth Hybridauth instance*/
	protected $hybridauth;
	/** @var Session Hybridauth session */
	protected $session;

	const PROVIDER_NAME_KEY = 'provider_key';

	public function __construct(array $config) {
		$authention_service_config = $config;
		$this->session = new Session();
		$this->hybridauth = new Hybridauth($authention_service_config, null, $this->session);
	}

	public function getProviderConfig($provider_name) {
		return $this->hybridauth->getProviderConfig($provider_name);
	}

	public function getConnectedProviders() {
		return $this->hybridauth->getConnectedProviders();
	}

	public function authenticate($provider_name) {
		return $this->hybridauth->authenticate($provider_name);
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