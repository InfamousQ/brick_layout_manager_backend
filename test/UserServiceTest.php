<?php

use Phinx\Wrapper\TextWrapper;
use InfamousQ\LManager\Services\UserService;

class UserServiceTest extends \PHPUnit\Framework\TestCase {

	/** @var TextWrapper $T */
	protected static $T;
	/** @var UserService $user_service */
	protected $user_service;

	public function setUp(): void{
		$app = new \Phinx\Console\PhinxApplication();
		$app->setAutoExit(false);

		self::$T = new TextWrapper($app);
		self::$T->setOption('configuration', '.deploy/php/phinx.php');
		self::$T->getMigrate("test");

		$db_settings = [
			'host' => getenv('DB_HOST'),
			'port' => getenv('DB_PORT'),
			'dbname' => getenv('DB_NAME'),
			'user' => getenv('DB_USER'),
			'password' => getenv('DB_PASS'),
		];
		$test_mapper_service = new \InfamousQ\LManager\Services\EntityMapperService($db_settings);
		$this->user_service = new UserService($test_mapper_service);
	}

	public function tearDown(): void {
		self::$T->getRollback("test", 0);
	}

	public function testRetrievingNonExistingEmailReturnsFalse() {
		$non_existing_email = 'nonexisting@dummy.test';

		$non_existing_id = $this->user_service->findUserIdByEmail($non_existing_email);
		$this->assertNull($non_existing_id);
	}

	public function testCreateUserAndRetrieveUserById() {
		$profile = new \Hybridauth\User\Profile();
		$profile->displayName = 'Test User Dummy 1';
		$profile->email = 'testuser1@dummy.test';

		$new_user = $this->user_service->createUserForProfile($profile);
		$fetched_user_entity = $this->user_service->getUserById($new_user->id);
		$this->assertTrue($new_user->id == $fetched_user_entity->id, 'User creation successful');
		$this->assertSame($profile->displayName, $new_user->name, 'User name correct');
		$this->assertSame($profile->email, $new_user->email, 'User email correct');
	}

	public function testCreateUserAndRetrieveUserByEmail() {
		$test_profile_email = 'testuser2@dummy.test';

		$profile = new \Hybridauth\User\Profile();
		$profile->displayName = 'Test User Dummy 2';
		$profile->email = $test_profile_email;

		$new_user = $this->user_service->createUserForProfile($profile);
		$email_user_id = $this->user_service->findUserIdByEmail($test_profile_email);

		$this->assertTrue($email_user_id == $new_user->id);
	}

	public function testCreateUserEditUserAndSaveUser() {
		$profile = new \Hybridauth\User\Profile();
		$profile->displayName = 'Test User Dummy 3';
		$profile->email = 'testuser3@dummy.test';

		$new_user = $this->user_service->createUserForProfile($profile);
		$this->assertSame($profile->displayName, $new_user->name, 'User\'s original name created');

		$changed_name = 'Test user foobar';
		$changed_email = 'testfooar@dummy.test';
		$new_user->name = $changed_name;
		$new_user->email = $changed_email;
		$this->assertTrue($this->user_service->saveUser($new_user), 'User saved to DB');

		$copy_of_user = $this->user_service->getUserById($new_user->id);
		$this->assertSame($new_user->name, $copy_of_user->name, 'User name change saved to DB');
		$this->assertSame($new_user->email, $copy_of_user->email, 'User email change saved to DB');
	}
}