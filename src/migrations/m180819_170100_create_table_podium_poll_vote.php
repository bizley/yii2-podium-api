<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Connection;
use yii\db\Migration;

class m180819_170100_create_table_podium_poll_vote extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_poll_vote}}', [
            'poll_id' => $this->integer()->notNull(),
            'answer_id' => $this->integer()->notNull(),
            'member_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk-podium_poll_vote', '{{%podium_poll_vote}}', ['poll_id', 'answer_id', 'member_id']);
        $this->addForeignKey(
            'fk-podium_poll_vote-poll_id',
            '{{%podium_poll_vote}}',
            'poll_id',
            '{{%podium_poll}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_poll_vote-answer_id',
            '{{%podium_poll_vote}}',
            'answer_id',
            '{{%podium_poll_answer}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_poll_vote-member_id',
            '{{%podium_poll_vote}}',
            'member_id',
            '{{%podium_member}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        return true;
    }

    public function down(): bool
    {
        $this->dropTable('{{%podium_poll_vote}}');
        return true;
    }
}
