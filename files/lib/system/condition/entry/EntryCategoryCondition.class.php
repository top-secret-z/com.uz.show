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
namespace show\system\condition\entry;

use InvalidArgumentException;
use show\data\entry\EntryList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiCategoryCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for the category an entry belongs to.
 */
class EntryCategoryCondition extends AbstractMultiCategoryCondition implements IObjectListCondition
{
    /**
     * @inheritDoc
     */
    protected $fieldName = 'entryCategoryIDs';

    /**
     * @inheritDoc
     */
    protected $label = 'show.entry.category';

    /**
     * @inheritDoc
     */
    public $objectType = 'com.uz.show.category';

    /**
     * @inheritDoc
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData)
    {
        if (!($objectList instanceof EntryList)) {
            throw new InvalidArgumentException("Object list is no instance of '" . EntryList::class . "', instance of '" . \get_class($objectList) . "' given.");
        }

        $objectList->getConditionBuilder()->add('entry.categoryID IN (?)', [$conditionData[$this->fieldName]]);
    }
}
