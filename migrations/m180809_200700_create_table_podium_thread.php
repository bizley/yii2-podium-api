<?php

declare(strict_types=1);

use yii\db\Migration;

class m180809_200700_create_table_podium_thread extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_thread}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'forum_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull(),
            'pinned' => $this->boolean()->notNull()->defaultValue(false),
            'locked' => $this->boolean()->notNull()->defaultValue(false),
            'posts_count' => $this->integer()->notNull()->defaultValue(0),
            'views_count' => $this->integer()->notNull()->defaultValue(0),
            'created_post_at' => $this->integer(),
            'updated_post_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-podium_thread-author_id',
            '{{%podium_thread}}', 'author_id',
            '{{%podium_member}}', 'id',
            'NO ACTION', 'CASCADE');
        $this->addForeignKey(
            'fk-podium_thread-category_id',
            '{{%podium_thread}}', 'category_id',
            '{{%podium_category}}', 'id',
            'CASCADE', 'CASCADE');
        $this->addForeignKey(
            'fk-podium_thread-forum_id',
            '{{%podium_thread}}', 'forum_id',
            '{{%podium_forum}}', 'id',
            'CASCADE', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_thread}}');
    }
}
