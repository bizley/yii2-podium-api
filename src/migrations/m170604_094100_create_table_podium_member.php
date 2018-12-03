<?php

declare(strict_types=1);

namespace bizley\podium\api\migrations;

use yii\db\Migration;

class m170604_094100_create_table_podium_member extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
            $statusId = 'ENUM("registered","active","banned") NOT NULL DEFAULT "registered"';
        } else {
            $statusId = $this->string(45)->notNull()->defaultValue(\bizley\podium\api\enums\MemberStatus::REGISTERED);
        }

        $this->createTable('{{%podium_member}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->string(255)->notNull()->unique(),
            'username' => $this->string(255)->notNull()->unique(),
            'slug' => $this->string(255)->notNull()->unique(),
            'status_id' => $statusId,
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], $tableOptions);
    }

    public function down(): void
    {
        $this->dropTable('{{%podium_member}}');
    }
}
