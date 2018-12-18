<?php

namespace InfamousQ\LManager\Services;


interface AuthenticationServiceInterface {

	public function __construct(array $config);

	public function getProviderConfig($provider_name);

	public function authenticate();

}