<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Connection;
use yii\db\Migration;

class m180816_190400_create_table_podium_bookmark extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_bookmark}}', [
            'member_id' => $this->integer()->notNull(),
            'thread_id' => $this->integer()->notNull(),
            'last_seen' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk-podium_bookmark', '{{%podium_bookmark}}', ['member_id', 'thread_id']);
        $this->addForeignKey(
            'fk-podium_bookmark-member_id',
            '{{%podium_bookmark}}',
            'member_id',
            '{{%podium_member}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_bookmark-thread_id',
            '{{%podium_bookmark}}',
            'thread_id',
            '{{%podium_thread}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    public function down(): bool
    {
        $this->dropTable('{{%podium_bookmark}}');
        return true;
    }
}
