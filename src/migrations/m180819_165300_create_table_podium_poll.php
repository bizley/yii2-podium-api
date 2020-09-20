<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use bizley\podium\api\enums\PollChoice;
use yii\db\Connection;
use yii\db\Migration;

class m180819_165300_create_table_podium_poll extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            $choiceId = 'ENUM("single","multiple") NOT NULL DEFAULT "single"';
        } else {
            $choiceId = $this->string(45)->notNull()->defaultValue(PollChoice::SINGLE);
        }

        $this->createTable('{{%podium_poll}}', [
            'id' => $this->primaryKey(),
            'thread_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'question' => $this->string(255)->notNull(),
            'revealed' => $this->boolean()->notNull()->defaultValue(true),
            'pinned' => $this->boolean()->notNull()->defaultValue(false),
            'choice_id' => $choiceId,
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'expires_at' => $this->integer(),
            'archived' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-podium_poll-thread_id',
            '{{%podium_poll}}',
            'thread_id',
            '{{%podium_thread}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_poll-author_id',
            '{{%podium_poll}}',
            'author_id',
            '{{%podium_member}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );

        return true;
    }

    public function down(): bool
    {
        $this->dropTable('{{%podium_poll}}');
        return true;
    }
}
