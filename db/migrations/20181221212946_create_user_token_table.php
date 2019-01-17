<?php


use Phinx\Migration\AbstractMigration;

class CreateUserTokenTable extends AbstractMigration {
	public function change() {
		$user_token_table = $this->table('user_token', ['id' => false, 'primary_key' => ['user_id', 'adapter_type']]);
		$user_token_table
			->addColumn('user_id', 'integer')
			->addColumn('adapter_type', 'integer')
			->addColumn('access_token', 'string')
			->addForeignKey(['user_id'], 'user')
			->addTimestamps()
			->create();
	}
}
