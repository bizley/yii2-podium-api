<?php

declare(strict_types=1);

use yii\db\Migration;

class m180106_052800_create_table_podium_acquaintance extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_acquaintance}}', [
            'id' => $this->primaryKey(),
            'member_id' => $this->integer()->notNull(),
            'target_id' => $this->integer()->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-podium_acquaintance-unique', '{{%podium_acquaintance}}', ['member_id', 'target_id', 'type'], true);
        $this->addForeignKey('fk-podium_acquaintance-member_id', '{{%podium_acquaintance}}', 'member_id', '{{%podium_member}}', 'id');
        $this->addForeignKey('fk-podium_acquaintance-target_id', '{{%podium_acquaintance}}', 'target_id', '{{%podium_member}}', 'id');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_acquaintance}}');
    }
}