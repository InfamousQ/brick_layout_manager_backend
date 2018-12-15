<?php


use Phinx\Migration\AbstractMigration;

class CreateUserTables extends AbstractMigration {
    public function change() {
        $user_table = $this->table('user');
        $user_table
            ->addColumn('name', 'string')
            ->addColumn('email', 'string')
            ->addIndex(['name', 'email'], ['unique' => true])
            ->addTimestamps()
            ->create();
    }
}
