<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Initializes RBAC tables.
 * Modified and merged "@yii/rbac/migrations" files.
 * Original authors:
 * @author Alexander Kochetov <creocoder@gmail.com>
 * @author Ivan Buttinoni <ivan.buttinoni@cibi.it>
 */
class m180718_212500_rbac_init extends Migration
{
    /**
     * @var string
     */
    public $itemTable = '{{%podium_auth_item}}';

    /**
     * @var string
     */
    public $itemChildTable = '{{%podium_auth_item_child}}';

    /**
     * @var string
     */
    public $assignmentTable = '{{%podium_auth_assignment}}';

    /**
     * @var string
     */
    public $ruleTable = '{{%podium_auth_rule}}';

    /**
     * @return bool
     */
    protected function isMSSQL(): bool
    {
        return $this->db->driverName === 'mssql' || $this->db->driverName === 'sqlsrv' || $this->db->driverName === 'dblib';
    }

    /**
     * @return bool
     */
    protected function isOracle(): bool
    {
        return $this->db->driverName === 'oci';
    }

    /**
     * @param string $delete
     * @param string $update
     * @return string
     */
    protected function buildFkClause(string $delete = '', string $update = ''): string
    {
        if ($this->isMSSQL()) {
            return '';
        }
        if ($this->isOracle()) {
            return ' ' . $delete;
        }
        return implode(' ', ['', $delete, $update]);
    }

    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->ruleTable, [
            'name' => $this->string(64)->notNull(),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
        ], $tableOptions);

        $this->createTable($this->itemTable, [
            'name' => $this->string(64)->notNull(),
            'type' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'rule_name' => $this->string(64),
            'data' => $this->binary(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'PRIMARY KEY ([[name]])',
            'FOREIGN KEY ([[rule_name]]) REFERENCES ' . $this->ruleTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE SET NULL', 'ON UPDATE CASCADE'),
        ], $tableOptions);
        $this->createIndex('idx-podium_auth_item-type', $this->itemTable, 'type');

        $this->createTable($this->itemChildTable, [
            'parent' => $this->string(64)->notNull(),
            'child' => $this->string(64)->notNull(),
            'PRIMARY KEY ([[parent]], [[child]])',
            'FOREIGN KEY ([[parent]]) REFERENCES ' . $this->itemTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
            'FOREIGN KEY ([[child]]) REFERENCES ' . $this->itemTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);

        $this->createTable($this->assignmentTable, [
            'item_name' => $this->string(64)->notNull(),
            'user_id' => $this->string(64)->notNull(),
            'created_at' => $this->integer(),
            'PRIMARY KEY ([[item_name]], [[user_id]])',
            'FOREIGN KEY ([[item_name]]) REFERENCES ' . $this->itemTable . ' ([[name]])' .
            $this->buildFkClause('ON DELETE CASCADE', 'ON UPDATE CASCADE'),
        ], $tableOptions);
        $this->createIndex('idx-podium_auth_assignment-user_id', $this->assignmentTable, 'user_id');

        if ($this->isMSSQL()) {
            $this->execute("CREATE TRIGGER dbo.trigger_podium_auth_item_child
            ON dbo.{$this->itemTable}
            INSTEAD OF DELETE, UPDATE
            AS
            DECLARE @old_name VARCHAR (64) = (SELECT name FROM deleted)
            DECLARE @new_name VARCHAR (64) = (SELECT name FROM inserted)
            BEGIN
            IF COLUMNS_UPDATED() > 0
                BEGIN
                    IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE {$this->itemChildTable} NOCHECK CONSTRAINT FK__podium_auth_item__child;
                        UPDATE {$this->itemChildTable} SET child = @new_name WHERE child = @old_name;
                    END
                UPDATE {$this->itemTable}
                SET name = (SELECT name FROM inserted),
                type = (SELECT type FROM inserted),
                description = (SELECT description FROM inserted),
                rule_name = (SELECT rule_name FROM inserted),
                data = (SELECT data FROM inserted),
                created_at = (SELECT created_at FROM inserted),
                updated_at = (SELECT updated_at FROM inserted)
                WHERE name IN (SELECT name FROM deleted)
                IF @old_name <> @new_name
                    BEGIN
                        ALTER TABLE {$this->itemChildTable} CHECK CONSTRAINT FK__podium_auth_item__child;
                    END
                END
                ELSE
                    BEGIN
                        DELETE FROM dbo.{$this->itemChildTable} WHERE parent IN (SELECT name FROM deleted) OR child IN (SELECT name FROM deleted);
                        DELETE FROM dbo.{$this->itemTable} WHERE name IN (SELECT name FROM deleted);
                    END
            END;");
        }
    }

    public function down(): void
    {
        if ($this->isMSSQL()) {
            $this->execute('DROP TRIGGER dbo.trigger_podium_auth_item_child;');
        }

        $this->dropTable($this->assignmentTable);
        $this->dropTable($this->itemChildTable);
        $this->dropTable($this->itemTable);
        $this->dropTable($this->ruleTable);
    }
}
