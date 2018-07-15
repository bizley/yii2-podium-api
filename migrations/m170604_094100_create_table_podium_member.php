<?php

declare(strict_types=1);

use yii\db\Migration;

class m170604_094100_create_table_podium_member extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        $statusId = $this->string(45)->notNull();
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            $statusId = 'ENUM("registered","active","banned") NOT NULL';
        }

        $this->createTable('{{%podium_member}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->string(255)->notNull()->unique(),
            'username' => $this->string(255)->notNull()->unique(),
            'status_id' => $statusId,
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_member}}');
    }
}
