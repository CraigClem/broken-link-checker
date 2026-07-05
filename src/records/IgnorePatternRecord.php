<?php

namespace craigclement\craftbrokenlinks\records;

use craft\db\ActiveRecord;

/**
 * IgnorePatternRecord represents a URL pattern that scans should skip.
 *
 * Patterns are stored in the database (not plugin settings / project config)
 * because they are runtime data curated by CP users — project config is
 * read-only on production sites with `allowAdminChanges` disabled.
 *
 * @property int $id
 * @property string $pattern
 * @property \DateTime|string $dateCreated
 * @property \DateTime|string $dateUpdated
 * @property string $uid
 *
 * @author Fell Mere
 * @since 1.2.0
 */
class IgnorePatternRecord extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%brokenlinks_ignorepatterns}}';
    }
}
