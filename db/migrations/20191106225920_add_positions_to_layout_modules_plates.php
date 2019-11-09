<?php

use Phinx\Migration\AbstractMigration;

class AddPositionsToLayoutModulesPlates extends AbstractMigration {

	public function change() {
		$layout_table = $this->table('layout');
		$layout_table
			->addColumn('w', 'integer', ['default' => 0])
			->addColumn('h', 'integer', ['default' => 0])
			->update();

		$module_table = $this->table('module');
		$module_table
			->addColumn('w', 'integer', ['default' => 0])
			->addColumn('h', 'integer', ['default' => 0])
			->update();
	}
}