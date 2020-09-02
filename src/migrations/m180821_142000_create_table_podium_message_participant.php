<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use bizley\podium\api\enums\MessageStatus;
use yii\db\Connection;
use yii\db\Migration;

class m180821_142000_create_table_podium_message_participant extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            $statusId = 'ENUM("new","read") NOT NULL DEFAULT "new"';
            $sideId = 'ENUM("sender","receiver") NOT NULL';
        } else {
            $statusId = $this->string(45)->notNull()->defaultValue(MessageStatus::NEW);
            $sideId = $this->string(45)->notNull();
        }

        $this->createTable('{{%podium_message_participant}}', [
            'message_id' => $this->integer(11)->notNull(),
            'member_id' => $this->integer(11)->notNull(),
            'status_id' => $statusId,
            'side_id' => $sideId,
            'archived' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk-podium_message_participant',
            '{{%podium_message_participant}}',
            ['message_id', 'member_id']
        );
        $this->addForeignKey(
            'fk-podium_message_participant-message_id',
            '{{%podium_message_participant}}',
            'message_id',
            '{{%podium_message}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_message_participant-member_id',
            '{{%podium_message_participant}}',
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
        $this->dropTable('{{%podium_message_participant}}');
        return true;
    }
}
