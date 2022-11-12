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

use PDO;
use show\data\category\ShowCategory;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\WCF;

/**
 * Represents a list of watched entrys.
 */
class WatchedEntryList extends ViewableEntryList
{
    /**
     * Creates a new WatchedEntryList object.
     */
    public function __construct()
    {
        parent::__construct();

        $categoryIDs = ShowCategory::getAccessibleCategoryIDs();
        if (empty($categoryIDs)) {
            $this->getConditionBuilder()->add('1=0');
        } else {
            $objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.entry');

            // add conditions
            $this->sqlConditionJoins = "LEFT JOIN show" . WCF_N . "_entry entry ON (entry.entryID = user_object_watch.objectID)";

            $this->getConditionBuilder()->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
            $this->getConditionBuilder()->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
            $this->getConditionBuilder()->add('entry.categoryID IN (?)', [$categoryIDs]);
            $this->getConditionBuilder()->add('entry.isDeleted = 0 AND entry.isDisabled = 0');
        }
    }

    /**
     * @inheritDoc
     */
    public function countObjects()
    {
        $sql = "SELECT    COUNT(*)
                FROM    wcf" . WCF_N . "_user_object_watch user_object_watch
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder()->__toString();
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($this->getConditionBuilder()->getParameters());

        return $statement->fetchSingleColumn();
    }

    /**
     * @inheritDoc
     */
    public function readObjectIDs()
    {
        $sql = "SELECT    user_object_watch.objectID AS objectID
                FROM    wcf" . WCF_N . "_user_object_watch user_object_watch
                " . $this->sqlConditionJoins . "
                " . $this->getConditionBuilder()->__toString() . "
                " . (!empty($this->sqlOrderBy) ? "ORDER BY " . $this->sqlOrderBy : '');
        $statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
        $statement->execute($this->getConditionBuilder()->getParameters());
        $this->objectIDs = $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        if ($this->objectIDs === null) {
            $this->readObjectIDs();
        }

        parent::readObjects();
    }
}
