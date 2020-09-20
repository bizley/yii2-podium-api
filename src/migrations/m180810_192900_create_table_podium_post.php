<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Connection;
use yii\db\Migration;

class m180810_192900_create_table_podium_post extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_post}}', [
            'id' => $this->primaryKey(),
            'thread_id' => $this->integer()->notNull(),
            'old_thread_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'edited' => $this->boolean()->notNull()->defaultValue(false),
            'pinned' => $this->boolean()->notNull()->defaultValue(false),
            'likes' => $this->integer()->notNull()->defaultValue(0),
            'dislikes' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'edited_at' => $this->integer(),
            'archived' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-podium_post-author_id',
            '{{%podium_post}}',
            'author_id',
            '{{%podium_member}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_post-thread_id',
            '{{%podium_post}}',
            'thread_id',
            '{{%podium_thread}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_post-old_thread_id',
            '{{%podium_post}}',
            'old_thread_id',
            '{{%podium_thread}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        return true;
    }

    public function down(): bool
    {
        $this->dropTable('{{%podium_post}}');
        return true;
    }
}
