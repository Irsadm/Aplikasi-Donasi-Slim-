<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
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
        $users = $this->table('users');
        $users->addColumn('name', 'string')
              ->addColumn('lokasi_id', 'integer', ['null' => true])
              ->addColumn('email', 'string')
              ->addColumn('password', 'string', ['null' => true])
              ->addColumn('phone', 'string', ['null' => true])
              ->addColumn('foto_profil', 'string', ['null' => true])
              ->addColumn('foto_verifikasi', 'string', ['null' => true])
              ->addColumn('biografi', 'string', ['null' => true])
              ->addColumn('status', 'integer', ['null' => true, 'limit' => 1, 'default' => '0'])
              ->addColumn('is_admin', 'integer', ['default' => 0])
              ->addColumn('deleted', 'integer', ['default' => 0])
              ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
              ->addColumn('updated_at', 'datetime', ['null' => true])
              ->addForeignKey('lokasi_id', 'locations', 'id',['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
              ->create();
    }
}
