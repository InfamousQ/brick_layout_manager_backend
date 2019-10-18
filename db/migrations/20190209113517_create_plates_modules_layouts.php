<?php


use Phinx\Migration\AbstractMigration;

class CreatePlatesModulesLayouts extends AbstractMigration {

	public function change() {
		$color_table = $this->table('color');
		$color_table
			->addColumn('name', 'string')
			->addColumn('hex', 'string')
			->create();


		$module_table = $this->table('module');
		$module_table
			->addColumn('name', 'string')
			->addColumn('public', 'boolean', ['default' => true])
			->addColumn('user_id', 'integer')
			->addForeignKey('user_id', 'user', 'id', ['delete' => 'CASCADE'])
			->addTimestamps()
			->create();

		$plate_table = $this->table('plate');
		$plate_table
			->addColumn('x', 'integer')
			->addColumn('y', 'integer')
			->addColumn('z', 'integer')
			->addColumn('h', 'integer')
			->addColumn('w', 'integer')
			->addColumn('color_id', 'integer')
			->addColumn('module_id', 'integer')
			->addForeignKey('color_id', 'color', 'id', ['delete' => 'SET_NULL'])
			->addForeignKey('module_id', 'module', 'id', ['delete' => 'CASCADE'])
			->addTimestamps()
			->create();

		$layout_table = $this->table('layout');
		$layout_table
			->addColumn('name', 'string')
			->addColumn('user_id', 'integer')
			->addForeignKey('user_id', 'user', 'id', ['delete' => 'SET_NULL'])
			->addTimestamps()
			->create();

		$layout_module_table = $this->table('layout_module');
		$layout_module_table
			->addColumn('layout_id', 'integer')
			->addColumn('module_id', 'integer')
			->addForeignKey('layout_id', 'layout', 'id', ['delete' => 'CASCADE'])
			->addForeignKey('module_id', 'module', 'id', ['delete' => 'CASCADE'])
			->addTimestamps()
			->create();
	}
}
