<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Migration;

class m180821_141600_create_table_podium_message extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_message}}', [
            'id' => $this->primaryKey(),
            'reply_to_id' => $this->integer(11),
            'subject' => $this->string(255)->notNull(),
            'content' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-podium_message-reply_to_id',
            '{{%podium_message}}', 'reply_to_id',
            '{{%podium_message}}', 'id',
            'NO ACTION', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_message}}');
    }
}
