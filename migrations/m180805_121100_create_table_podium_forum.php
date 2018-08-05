<?php

declare(strict_types=1);

use yii\db\Migration;

class m180805_121100_create_table_podium_forum extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_forum}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull()->unique(),
            'slug' => $this->string(255)->notNull()->unique(),
            'description' => $this->text(),
            'visible' => $this->boolean()->defaultValue(true),
            'sort' => $this->smallInteger()->notNull()->defaultValue(0),
            'threads_count' => $this->integer()->notNull()->defaultValue(0),
            'posts_count' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk-podium_forum-author_id', '{{%podium_forum}}', 'author_id', '{{%podium_member}}', 'id', 'NO ACTION', 'CASCADE');
        $this->addForeignKey('fk-podium_forum-category_id', '{{%podium_forum}}', 'category_id', '{{%podium_category}}', 'id', 'NO ACTION', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_forum}}');
    }
}
