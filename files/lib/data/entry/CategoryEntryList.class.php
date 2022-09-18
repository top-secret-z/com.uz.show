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

use show\data\category\ShowCategory;
use wcf\system\exception\SystemException;

/**
 * Represents a list of entrys in specific categories.
 */
class CategoryEntryList extends AccessibleEntryList
{
    /**
     * @inheritDoc
     */
    protected $applyCategoryFilter = false;

    /**
     * Creates a new CategoryEntryList object.
     */
    public function __construct($categoryID, $includeChildCategories = false)
    {
        parent::__construct();

        $categoryIDs = [$categoryID];

        if ($includeChildCategories) {
            $category = ShowCategory::getCategory($categoryID);

            if ($category === null) {
                throw new SystemException("invalid category id '" . $categoryID . "' given");
            }
            foreach ($category->getAllChildCategories() as $category) {
                if ($category->isAccessible()) {
                    $categoryIDs[] = $category->categoryID;
                }
            }
        }

        if (!SHOW_CATEGORY_ENABLE) {
            $this->getConditionBuilder()->add('entry.categoryID IN (?)', [$categoryIDs]);
        }

        // add addtitional categories
        if (SHOW_CATEGORY_ENABLE) {
            $this->getConditionBuilder()->add('(entry.categoryID IN (?) OR entry.entryID IN (SELECT entryID FROM show' . WCF_N . '_entry_to_category WHERE categoryID IN (?)))', [$categoryIDs, $categoryIDs]);
        }
    }
}
