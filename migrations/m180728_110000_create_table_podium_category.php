<?php

declare(strict_types=1);

use yii\db\Migration;

class m180728_110000_create_table_podium_category extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_category}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull()->unique(),
            'slug' => $this->string(255)->notNull()->unique(),
            'description' => $this->text(),
            'visible' => $this->boolean()->defaultValue(true),
            'sort' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_category}}');
    }
}
