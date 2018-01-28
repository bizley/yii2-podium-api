<?php

namespace bizley\podium\api\migrations;

use yii\db\Migration;

class m180128_113200_create_table_podium_permission extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_permission}}', [
            'member_id' => $this->integer()->notNull(),
            'permission' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey('primarykey', '{{%podium_permission}}', ['member_id', 'permission']);

        $this->addForeignKey('fk-podium_permission-member_id', '{{%podium_permission}}', 'member_id', '{{%podium_member}}', 'id');
    }

    public function down()
    {
        $this->dropTable('{{%podium_permission}}');
    }
}