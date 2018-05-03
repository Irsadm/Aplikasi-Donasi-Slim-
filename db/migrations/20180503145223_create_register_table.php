<?php

use Phinx\Migration\AbstractMigration;

class CreateRegisterTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
            $register = $this->table('registers');
            $register->addColumn('user_id', 'integer')
                          ->addColumn('token', 'string')
                          ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0])
                          ->addColumn('expired_date', 'datetime')
                          ->addForeignKey('user_id', 'users', 'id', ['update' => 'CASCADE', 'delete' => 'NO_ACTION'])
                          ->create();
    }
}
