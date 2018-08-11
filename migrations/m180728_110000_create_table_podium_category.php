<?php

declare(strict_types=1);

use yii\db\Migration;

class m180728_110000_create_table_podium_category extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_category}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'visible' => $this->boolean()->notNull()->defaultValue(true),
            'sort' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addForeignKey('fk-podium_category-author_id', '{{%podium_category}}', 'author_id', '{{%podium_member}}', 'id', 'NO ACTION', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_category}}');
    }
}
