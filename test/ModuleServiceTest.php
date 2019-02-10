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
		$mapper_service = new \InfamousQ\LManager\Services\EntityMapperService($db_settings);
		$this->module_service = new \InfamousQ\LManager\Services\ModuleService($mapper_service);
		$this->user_service = new \InfamousQ\LManager\Services\UserService($mapper_service);
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
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user', 'email' => 'test@test.test']);
		$new_module = $this->module_service->createModule($new_module_name, $author_user->id);
		$this->assertNotNull($new_module->id, 'Module id not null');
		$this->assertSame($new_module_name, $new_module->name, 'Module name correct');
		$this->assertTrue($author_user->id == $new_module->user->id, 'Module author\'s user id correct');
	}

	public function testCreateModuleEditModuleAndRetrieveItFromDB() {
		$new_module_name = 'Test module #2';
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 2', 'email' => 'test@test.test']);
		$new_module = $this->module_service->createModule($new_module_name, $author_user->id);
		$this->assertNotNull($new_module->id, 'Module id is not null');
		$edited_module_name = 'Test module #2 edited';
		$new_module->name = $edited_module_name;
		$this->assertTrue($this->module_service->saveModule($new_module));
		$edited_module = $this->module_service->getModuleById($new_module->id);
		$this->assertSame($edited_module_name, $edited_module->name, 'Module name editing successful');
	}

	public function testAddPlateToModule() {
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 3', 'email' => 'test3@test.test']);
		$module = $this->module_service->createModule('Test module #3', $author_user->id);
		$this->assertTrue($this->module_service->saveModule($module));

		$color = $this->module_service->createColor('test color', 'test');
		$this->assertTrue($this->module_service->saveColor($color), 'Color created');

		$plate = $this->module_service->createPlate(1, 2, 3, 2, 2, $color->id, $module->id);
		$this->assertTrue($this->module_service->savePlate($plate), 'Plate created');

		$module = $this->module_service->getModuleById($module->id);
		$this->assertSame(1, $module->plates->count(), 'Module has plate');
		$this->assertTrue($plate->id == $module->plates[0]->id, 'Module\'s plate is created plate');
	}
}