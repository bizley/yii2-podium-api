<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Connection;
use yii\db\Migration;

class m180814_201400_create_table_podium_thumb extends Migration
{
    public function up(): bool
    {
        $tableOptions = null;
        /** @var Connection $db */
        $db = $this->db;
        if ($db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%podium_thumb}}', [
            'member_id' => $this->integer()->notNull(),
            'post_id' => $this->integer()->notNull(),
            'thumb' => $this->tinyInteger(1)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey('pk-podium_thumb', '{{%podium_thumb}}', ['member_id', 'post_id']);
        $this->addForeignKey(
            'fk-podium_thumb-member_id',
            '{{%podium_thumb}}',
            'member_id',
            '{{%podium_member}}',
            'id',
            'NO ACTION',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-podium_thumb-post_id',
            '{{%podium_thumb}}',
            'post_id',
            '{{%podium_post}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    public function down(): bool
    {
        $this->dropTable('{{%podium_thumb}}');
        return true;
    }
}
