<?php

declare(strict_types=1);

use yii\db\Migration;

class m180819_165300_create_table_podium_poll extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            $choiceId = 'ENUM("single","multiple") NOT NULL DEFAULT "single"';
        } else {
            $choiceId = $this->string(45)->notNull()->defaultValue(\bizley\podium\api\enums\PollChoice::SINGLE);
        }

        $this->createTable('{{%podium_poll}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'question' => $this->string(255)->notNull(),
            'revealed' => $this->boolean()->notNull()->defaultValue(true),
            'type_id' => $choiceId,
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'expires_at' => $this->integer(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-podium_poll-post_id',
            '{{%podium_poll}}', 'post_id',
            '{{%podium_post}}', 'id',
            'CASCADE', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_poll}}');
    }
}
