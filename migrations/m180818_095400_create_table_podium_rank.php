<?php

declare(strict_types=1);

use yii\db\Migration;

class m180818_095400_create_table_podium_rank extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_rank}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'min_posts' => $this->integer()->notNull()->unique(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_rank}}');
    }
}
