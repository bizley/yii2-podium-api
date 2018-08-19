<?php

declare(strict_types=1);

use yii\db\Migration;

class m180819_101000_create_table_podium_group_member extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_group_member}}', [
            'member_id' => $this->integer()->notNull(),
            'group_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk-podium_group_member', '{{%podium_group_member}}', ['member_id', 'group_id']);
        $this->addForeignKey(
            'fk-podium_group_member-member_id',
            '{{%podium_group_member}}', 'member_id',
            '{{%podium_member}}', 'id',
            'CASCADE', 'CASCADE');
        $this->addForeignKey(
            'fk-podium_group_member-group_id',
            '{{%podium_group_member}}', 'group_id',
            '{{%podium_group}}', 'id',
            'CASCADE', 'CASCADE');
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_group_member}}');
    }
}
