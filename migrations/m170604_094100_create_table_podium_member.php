<?php

namespace bizley\podium\api\migrations;

use yii\db\Migration;

class m170604_094100_create_table_podium_member extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_member}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),
            'slug' => $this->string()->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%podium_member}}');
    }
}