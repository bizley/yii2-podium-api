<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Connection;
use yii\db\Migration;

class m180812_102800_create_table_podium_subscription extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_subscription}}', [
            'member_id' => $this->integer()->notNull(),
            'thread_id' => $this->integer()->notNull(),
            'seen' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk-podium_subscription', '{{%podium_subscription}}', ['member_id', 'thread_id']);
        $this->addForeignKey(
            'fk-podium_subscription-member_id',
            '{{%podium_subscription}}',
            'member_id',
            '{{%podium_member}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_subscription-thread_id',
            '{{%podium_subscription}}',
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
        $this->dropTable('{{%podium_subscription}}');
        return true;
    }
}
