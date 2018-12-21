<?php

use Phinx\Wrapper\TextWrapper;
use InfamousQ\LManager\Services\UserService;

class UserServiceTest extends \PHPUnit\Framework\TestCase {

	/** @var TextWrapper $T */
	protected static $T;
	/** @var UserService $user_service */
	protected $user_service;

	public function setUp(){
		$app = new \Phinx\Console\PhinxApplication();
		$app->setAutoExit(false);

		self::$T = new TextWrapper($app);
		self::$T->setOption('configuration', '.deploy/phinx.php');
		self::$T->getMigrate("test");

		$db_settings = [
			'host' => 'bl_db',
			'port' => 5432,
			'dbname' => 'lmanager_test',
			'user' => getenv('PHINX_TEST_DB_USER'),
			'password' => getenv('PHINX_TEST_DB_PASS'),
		];
		$test_db_service = new \InfamousQ\LManager\Services\PDODatabaseService($db_settings);
		$this->user_service = new UserService($test_db_service);
	}

	public function tearDown(){
		self::$T->setOption('configuration', '.deploy/phinx.php');
		self::$T->getRollback("test", 0);
	}

	public function testRetrievingNonExistingEmailReturnsFalse() {
		$non_existing_email = 'nonexisting@dummy.test';

		$non_existing_id = $this->user_service->findUserIdByEmail($non_existing_email);
		$this->assertFalse($non_existing_id);
	}

	public function testCreateUserAndRetrieveUserByEmail() {
		$test_profile_email = 'testuser@dummy.test';

		$profile = new \Hybridauth\User\Profile();
		$profile->displayName = 'Test User Dummy';
		$profile->email = $test_profile_email;

		$new_user_id = $this->user_service->createUserForProfile($profile);
		$email_user_id = $this->user_service->findUserIdByEmail($test_profile_email);

		$this->assertSame($email_user_id, $new_user_id);
	}
}