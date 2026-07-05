<?php

namespace craigclement\craftbrokenlinks\migrations;

use craft\db\Migration;

/**
 * Adds the ignore-patterns table, which stores URL patterns that scans
 * should skip.
 */
class m260705_120000_add_ignore_patterns_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $table = '{{%brokenlinks_ignorepatterns}}';

        if (!$this->db->tableExists($table)) {
            $this->createTable($table, [
                'id' => $this->primaryKey(),
                'pattern' => $this->string(255)->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, $table, ['pattern(191)'], true);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%brokenlinks_ignorepatterns}}');

        return true;
    }
}
