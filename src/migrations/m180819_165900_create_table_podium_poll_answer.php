<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Connection;
use yii\db\Migration;

class m180819_165900_create_table_podium_poll_answer extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_poll_answer}}', [
            'id' => $this->primaryKey(),
            'poll_id' => $this->integer()->notNull(),
            'answer' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-podium_poll_answer-poll_id',
            '{{%podium_poll_answer}}',
            'poll_id',
            '{{%podium_poll}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    public function down(): bool
    {
        $this->dropTable('{{%podium_poll_answer}}');
        return true;
    }
}
