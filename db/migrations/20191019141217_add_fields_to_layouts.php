<?php


use Phinx\Migration\AbstractMigration;

class AddFieldsToLayouts extends AbstractMigration {

	public function change() {
		$layout_table = $this->table('layout');
		$layout_table
			->addColumn('public', 'boolean', ['default' => false])
			->update();
	}
}
