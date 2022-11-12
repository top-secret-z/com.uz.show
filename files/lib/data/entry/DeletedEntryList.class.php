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
use wcf\system\clipboard\ClipboardHandler;

/**
 * Represents a list of deleted entrys.
 */
class DeletedEntryList extends ViewableEntryList
{
    /**
     * Creates a new DeletedEntryList object.
     */
    public function __construct()
    {
        parent::__construct();

        // categories
        $accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
        if (!empty($accessibleCategoryIDs)) {
            $this->getConditionBuilder()->add('entry.categoryID IN (?)', [$accessibleCategoryIDs]);
        } else {
            $this->getConditionBuilder()->add('1=0');
        }

        $this->getConditionBuilder()->add('entry.isDeleted = ?', [1]);
    }

    /**
     * Returns the number of marked items.
     */
    public function getMarkedItems()
    {
        return ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.show.entry'));
    }
}
