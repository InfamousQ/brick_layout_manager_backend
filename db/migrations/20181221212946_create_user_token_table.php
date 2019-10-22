<?php


use Phinx\Migration\AbstractMigration;

class CreateUserTokenTable extends AbstractMigration {
	public function change() {
		$user_token_table = $this->table('user_token');
		$user_token_table
			->addColumn('user_id', 'integer')
			->addColumn('adapter_type', 'integer')
			->addColumn('access_token', 'string')
			->addForeignKey(['user_id'], 'users')
			->addIndex(['user_id', 'adapter_type'], ['unique' => true])
			->addTimestamps()
			->create();
	}
}
