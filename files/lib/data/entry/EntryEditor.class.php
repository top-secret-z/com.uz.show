<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace show\data\entry;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit entrys.
 */
class EntryEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Entry::class;

    /**
     * Updates the entry counter of the given users.
     */
    public static function updateEntryCounter(array $users)
    {
        $sql = "UPDATE    wcf" . WCF_N . "_user
                SET        showEntrys = showEntrys + ?
                WHERE    userID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        foreach ($users as $userID => $entrys) {
            $statement->execute([$entrys, $userID]);
        }
    }

    /**
     * Updates additional categories (IDs).
     */
    public function updateCategories(array $categoryIDs = [])
    {
        // remove old first
        $sql = "DELETE FROM    show" . WCF_N . "_entry_to_category
                WHERE        entryID = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->entryID]);

        // add new categories, if configured
        // always add main category
        if (!\in_array($this->categoryID, $categoryIDs)) {
            $categoryIDs[] = $this->categoryID;
        }

        if (!empty($categoryIDs) && SHOW_CATEGORY_ENABLE) {
            WCF::getDB()->beginTransaction();

            $sql = "INSERT IGNORE INTO    show" . WCF_N . "_entry_to_category
                        (categoryID, entryID)
                    VALUES        (?, ?)";
            $statement = WCF::getDB()->prepareStatement($sql);
            foreach ($categoryIDs as $categoryID) {
                $statement->execute([$categoryID, $this->entryID]);
            }
            WCF::getDB()->commitTransaction();
        }
    }
}
