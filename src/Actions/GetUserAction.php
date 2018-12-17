<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 18.12.2018
 * Time: 0:11
 */

namespace InfamousQ\LManager\Actions;


use Hybridauth\Adapter\AdapterInterface;
use Hybridauth\Hybridauth;
use Hybridauth\User\Profile;
use InfamousQ\LManager\Services\UserService;
use Slim\Http\Request;
use Slim\Http\Response;

class GetUserAction extends AbstractAction {

	public function __invoke(Request $request, Response $response) {
		/** @var Hybridauth $auth */
		$auth = $this->container->get('auth');
		$connected_providers = $auth->getConnectedProviders();
		/** @var AdapterInterface $active_adapter */
		$active_adapter = null;
		/** @var Profile $user_profile */
		$user_profile = null;
		/** @var int $user_id */
		$user_id = null;
		if (count($connected_providers) > 0) {
			$active_adapter = $auth->getAdapter($connected_providers[0]);
			$user_profile = $active_adapter->getUserProfile();
			$user_id = UserService::findUserIdByEmail($user_profile->email);
		}
		// TODO: Should active adapter work as middleware?
		return $this->container->get('view')->render('user::login', ['profile' => $user_profile, 'user_id' => $user_id]);
	}

}