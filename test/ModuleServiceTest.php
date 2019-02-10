<?php

use InfamousQ\LManager\Models\User;
use InfamousQ\LManager\Models\Module;

class ModuleServiceTest extends \PHPUnit\Framework\TestCase {

	/** @var \Phinx\Wrapper\TextWrapper */
	protected static $T;
	/** @var \InfamousQ\LManager\Services\ModuleService */
	protected $module_service;
	/** @var \InfamousQ\LManager\Services\UserService */
	protected $user_service;

	public function setUp() {
		$app = new \Phinx\Console\PhinxApplication();
		$app->setAutoExit(false);

		self::$T = new \Phinx\Wrapper\TextWrapper($app);
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
		$this->module_service = new \InfamousQ\LManager\Services\ModuleService($test_db_service);
		$this->user_service = new \InfamousQ\LManager\Services\UserService($test_db_service);
	}

	public function tearDown(){
		self::$T->setOption('configuration', '.deploy/phinx.php');
		self::$T->getRollback("test", 0);
	}

	public function testRetrievingModuleWithNonExistingIdReturnsFalse() {
		$non_existing_module_id = $this->module_service->getModuleById(12);
		$this->assertNull($non_existing_module_id, 'Faulty id returns null');
	}

	public function testCreateModuleAndRetrieveItFromDBUsingId() {
		$new_module_name = 'Test module #1';
		$author_user_id = $this->user_service->createUserForUser(new User(null, 'test@test.test', 'Tester'));
		$new_module = $this->module_service->createModule($new_module_name, $author_user_id);
		$this->assertNotNull($new_module->id, 'Module id not null');
		$this->assertSame($new_module_name, $new_module->name, 'Module name correct');
		$this->assertSame($author_user_id, $new_module->user_id, 'Module author\'s user id correct');
	}
}