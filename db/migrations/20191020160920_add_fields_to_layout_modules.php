<?php


use Phinx\Migration\AbstractMigration;

class AddFieldsToLayoutModules extends AbstractMigration {

	public function change() {
		$layout_table = $this->table('layout_module');
		$layout_table
			->addColumn('x', 'integer')
			->addColumn('y', 'integer')
			->update();
	}
}
