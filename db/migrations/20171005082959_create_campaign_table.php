<?php

use Phinx\Migration\AbstractMigration;

class CreateCampaignTable extends AbstractMigration
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
        $campaign = $this->table('campaigns');
        $campaign->addColumn('title', 'string')
                 ->addColumn('category_id', 'integer')
                 ->addColumn('user_id', 'integer')
                 ->addColumn('lokasi_penerima', 'integer')
                 ->addColumn('target_dana', 'integer')
                 ->addColumn('deadline', 'datetime')
                 ->addColumn('deskripsi_singkat', 'string')
                 ->addColumn('deskripsi_lengkap', 'text')
                 ->addColumn('cover', 'string', ['null' => true])
                 ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                 ->addColumn('updated_at', 'datetime', ['null' => true])
                 ->addColumn('status', 'integer', ['limit' => 1, 'default' => 0])
                 ->addForeignKey('category_id', 'campaign_category','id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                 ->addForeignKey('lokasi_penerima', 'locations','id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                 ->addForeignKey('user_id', 'users','id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                 ->create();
    }
}
