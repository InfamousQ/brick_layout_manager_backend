<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 18.12.2018
 * Time: 0:11
 */

namespace InfamousQ\LManager\Actions;


use Hybridauth\Adapter\AdapterInterface;
use Hybridauth\User\Profile;
use Slim\Http\Request;
use Slim\Http\Response;

class GetUserAction {

	/** @var \InfamousQ\LManager\Services\AuthenticationServiceInterface $auth_service */
	protected $auth_service;
	/** @var \InfamousQ\LManager\Services\UserServiceInterface $user_service */
	protected $user_service;
	/** @var  \League\Plates\Engine $view */
	protected $view;

	public function __construct(\Slim\Container $container) {
		$this->auth_service = $container->get('auth');
		$this->user_service = $container->get('user');
		$this->view = $container->get('view');
	}

	public function __invoke(Request $request, Response $response) {
		$connected_providers = $this->auth_service->getConnectedProviders();
		/** @var AdapterInterface $active_adapter */
		$active_adapter = null;
		/** @var Profile $user_profile */
		$user_profile = null;
		/** @var int $user_id */
		$user_id = null;
		if (count($connected_providers) > 0) {
			$active_adapter = $this->auth_service->getAdapter($connected_providers[0]);
			$user_profile = $active_adapter->getUserProfile();
			$user_id = $this->user_service->findUserIdByEmail($user_profile->email);
		}
		// TODO: Should active adapter work as middleware?
		return $this->view->render('user::login', ['profile' => $user_profile, 'user_id' => $user_id]);
	}

}