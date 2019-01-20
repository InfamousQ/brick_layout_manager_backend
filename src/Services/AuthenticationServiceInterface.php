<?php

namespace InfamousQ\LManager\Services;

use Hybridauth\Adapter\AbstractAdapter;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;

interface AuthenticationServiceInterface {

	public function __construct(array $config);

	/**
	 * Fetch configuration for given provider $provider_name
	 * @param string $provider_name
	 * @return array
	 * @throws UnexpectedValueException If given $provider_name is found but configuration is not valid
	 * @throws InvalidArgumentException If given $provider_name is not found from configuration.
	 */
	public function getProviderConfig($provider_name);

	/**
	 * Fetch Adapter that matches given $provider_name
	 * @param string $provider_name
	 * @return AbstractAdapter
	 */
	public function getAdapter($provider_name);

	/**
	 * Fetch Adapter that matches given $provider_name service and authenticate user.
	 * @param string $provider_name
	 * @return AbstractAdapter
	 */
	public function authenticate($provider_name);

	/**
	 * Disconnect current user from all connected adapters
	 * @return boolean Was disconnection successful?
	 */
	public function disconnectAllAdapters();

	/** Get name, code and icon of all available providers
	 * @return array
	 */
	public function getAvailableProviders();

	/**
	 * Get names of all connected providers
	 * @return string[]
	 */
	public function getConnectedProviders();

	/**
	 * Set given $provider_name to storage
	 * @param $provider_name
	 */
	public function setProviderToStorage($provider_name);

	/**
	 * @return string Provider's name read from storage
	 */
	public function getProviderFromStorage();
}