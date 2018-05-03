<?php

use Phinx\Migration\AbstractMigration;

class CreateDonasiTable extends AbstractMigration
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
        $donasi = $this->table('donasi');
        $donasi->addColumn('campaign_id', 'integer', ['null' => true])
               ->addColumn('user_id', 'integer', ['null' => true])
               ->addColumn('nominal', 'integer', ['null' => true])
               ->addColumn('komentar', 'string', ['null' => true])
               ->addColumn('status', 'integer', ['default' => 0,])
               ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
               ->addForeignKey('campaign_id', 'campaigns', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
               ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
               ->create();
    }
}
