<?php

declare(strict_types=1);

use yii\db\Migration;

class m180821_142000_create_table_podium_message_participant extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            $statusId = 'ENUM("new","read") DEFAULT "new"';
        } else {
            $statusId = $this->string(45)->notNull()->defaultValue(\bizley\podium\api\enums\MessageStatus::NEW);
        }

        $this->createTable('{{%podium_message_participant}}', [
            'message_id' => $this->integer(11)->notNull(),
            'member_id' => $this->integer(11)->notNull(),
            'status_id' => $statusId,
            'archived' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk-podium_message_participant', '{{%podium_message_participant}}', ['message_id', 'member_id']);
        $this->addForeignKey(
            'fk-podium_message_participant-message_id',
            '{{%podium_message_participant}}', 'message_id',
            '{{%podium_message}}', 'id',
            'CASCADE', 'CASCADE');
        $this->addForeignKey(
            'fk-podium_message_participant-member_id',
            '{{%podium_message_participant}}', 'member_id',
            '{{%podium_member}}', 'id',
            'NO ACTION', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_message_participant}}');
    }
}
