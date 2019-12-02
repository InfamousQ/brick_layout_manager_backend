<?php

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
		self::$T->setOption('configuration', '.deploy/php/phinx.php');
		self::$T->getMigrate("test");

		$db_settings = [
			'host' => getenv('DB_HOST'),
			'port' => getenv('DB_PORT'),
			'dbname' => getenv('DB_NAME'),
			'user' => getenv('DB_USER'),
			'password' => getenv('DB_PASS'),
		];
		$mapper_service = new \InfamousQ\LManager\Services\EntityMapperService($db_settings);
		$this->module_service = new \InfamousQ\LManager\Services\ModuleService($mapper_service);
		$this->user_service = new \InfamousQ\LManager\Services\UserService($mapper_service);
	}

	public function tearDown(){
		self::$T->setOption('configuration', '.deploy/php/phinx.php');
		self::$T->getRollback("test", 0);
	}

	public function testRetrievingModuleWithNonExistingIdReturnsNull() {
		$non_existing_module = $this->module_service->getModuleById(12);
		$this->assertNull($non_existing_module, 'Faulty id returns null');
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

	public function testCreateModuleEditModuleSaveModuleAndDeleteModule() {
		$new_module_name = 'Test module #3';
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 3', 'email' => 'test@test.test']);
		$new_module = $this->module_service->createModule($new_module_name, $author_user->id);
		$this->assertNotNull($new_module->id, 'Module id is not null');
		$edited_module_name = 'Test module #3 edited';
		$new_module->name = $edited_module_name;
		$this->module_service->saveModule($new_module);
		$edited_module = $this->module_service->getModuleById($new_module->id);
		$this->assertTrue($this->module_service->deleteModuleById($edited_module->id),'Deletion successful');
		$this->assertFalse($this->module_service->deleteModuleById($edited_module->id),'Deletion fails if id already deleted');
		$this->assertEmpty($this->module_service->getModuleById($edited_module->id), 'Could not fetch deleted module via id');

		$new_module2 = $this->module_service->createModule($new_module_name . 'fefe', $author_user->id);
		$this->assertTrue($this->module_service->deleteModuleById($new_module2->id), 'Deletion of second module successful');
		$this->assertEmpty($this->module_service->getModuleById($edited_module->id), 'Could not fetch deleted module via id');
	}

	// Plate tests
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

	public function testAddPlateToModuleEditPlate() {
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 4', 'email' => 'test4@test.test']);
		$module = $this->module_service->createModule('Test module #4', $author_user->id);
		$original_color = $this->module_service->createColor('Original', '000000');
		$plate = $this->module_service->createPlate(1, 2, 3, 2, 2, $original_color->id, $module->id);
		$this->assertTrue($this->module_service->savePlate($plate), 'Plate created');

		$new_y = 10;
		$new_w = 12;
		$new_color = $this->module_service->createColor('New', 'ffffff');
		$plate->y = $new_y;
		$plate->w = $new_w;
		$plate->color_id = $new_color->id;
		$this->assertTrue($this->module_service->savePlate($plate), 'Plate modified');

		$copy_of_plate = $this->module_service->getPlateById($plate->id);
		$this->assertEquals($new_y, $copy_of_plate->y, 'Value modified');
		$this->assertEquals($new_color->id, $copy_of_plate->color->id, 'Value modified');
	}

	public function testAddPlateToModuleDeletePlate() {
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user #6', 'email' => 'test6@test.test']);
		$module = $this->module_service->createModule('Test module #6', $author_user->id);
		$color = $this->module_service->createColor('test color #2', 'fefefe');
		$plate = $this->module_service->createPlate(1,2,3,5,5, $color->id, $module->id);

		$this->assertTrue($this->module_service->savePlate($plate), 'Plate saved');
		$module = $this->module_service->getModuleById($module->id);
		$this->assertSame(1, $module->plates->count(), 'Module has plate');

		$this->assertTrue($this->module_service->deletePlateById($plate->id), 'Deletion successful');
		$this->assertNull($this->module_service->getPlateById($plate->id), 'Could not fetch deleted plate via id');
		$module = $this->module_service->getModuleById($module->id);
		$this->assertSame(0, $module->plates->count(), 'Module has no plates');
	}

	// Layout tests
	public function testRetrievingLayoutWithNonExistingIdReturnsNull() {
		$non_existing_layout = $this->module_service->getLayoutById(12);
		$this->assertNull($non_existing_layout, 'Faulty id returns null');
	}

	public function testCreateLayout() {
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 4', 'email' => 'test4@test.test']);
		$new_layout = $this->module_service->createLayout('Demo layout', $author_user->id);
		$this->assertNotNull($new_layout->id, 'Layout created with minimal information');
	}

	public function testCreateLayoutAndAddModuleToIt() {
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 5', 'email' => 'test5@test.test']);
		$layout = $this->module_service->createLayout('Demo layout 2', $author_user->id);
		$this->assertNotNull($layout->id, 'Layout created');

		$module = $this->module_service->createModule('Test module #4', $author_user->id);
		$this->module_service->saveModule($module);

		$this->assertTrue($this->module_service->connectModuleToLayout($layout, $module,5, 8));
		$layout = $this->module_service->getLayoutById($layout->id);
		$this->assertSame(1, $layout->modules->count(), 'Layout has module');
		$this->assertTrue($module->id == $layout->modules[0]->id, 'Layout\'s module is created module');

		$author_user = $this->user_service->getUserById($author_user->id);
		$this->assertSame(1, $author_user->layouts->count(), 'User has layout');
		$this->assertTrue($layout->id == $author_user->layouts[0]->id, 'User\'s layout is created layout');
	}

	public function testCreateLayoutAddModuleToItDeleteLayout() {
		$author_user = $this->user_service->createUserFromArray(['name' => 'Test user 6', 'email' => 'test6@test.test']);
		$layout = $this->module_service->createLayout('Demo layout 3', $author_user->id);
		$module = $this->module_service->createModule('Test module #5', $author_user->id);
		$this->module_service->connectModuleToLayout( $layout, $module, 10, 20);

		$layout = $this->module_service->getLayoutById($layout->id);
		$module = $this->module_service->getModuleById($module->id);
		$author_user = $this->user_service->getUserById($author_user->id);

		$this->assertSame(1, $layout->modules->count(), 'Layout has module');
		$this->assertSame(1, $module->layouts->count(), 'Module has layout');
		$this->assertSame(1, $author_user->layouts->count(), 'User has layout');

		$this->assertTrue($this->module_service->deleteLayoutById($layout->id), 'Deletion successful');

		$this->assertNull($this->module_service->getLayoutById($layout->id), 'Layout not found by id after deletion');
		$module = $this->module_service->getModuleById($module->id);
		$this->assertSame(0, $module->layouts->count(), 'Module does not have layouts');
		$author_user = $this->user_service->getUserById($author_user->id);
		$this->assertSame(0, $author_user->layouts->count(), 'User does not have layouts');
	}
}