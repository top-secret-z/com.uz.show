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
namespace show\data\category;

use wcf\data\category\Category;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\SingletonFactory;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Manages the show category cache.
 */
class ShowCategoryCache extends SingletonFactory
{
    /**
     * number of entrys / unread entrys
     */
    protected $entrys;

    protected $unreadEntrys;

    /**
     * Counts entrys in this category and its children.
     */
    protected function countEntrys(array &$categoryToParent, array &$entrys, $categoryID, array &$result)
    {
        $count = (isset($entrys[$categoryID])) ? $entrys[$categoryID] : 0;

        if (isset($categoryToParent[$categoryID])) {
            foreach ($categoryToParent[$categoryID] as $childCategoryID) {
                if (ShowCategory::getCategory($childCategoryID)->getPermission('canViewCategory')) {
                    $count += $this->countEntrys($categoryToParent, $entrys, $childCategoryID, $result);
                }
            }
        }

        if ($categoryID) {
            $result[$categoryID] = $count;
        }

        return $count;
    }

    /**
     * Returns entry count for given category.
     */
    public function getEntrys($categoryID)
    {
        if ($this->entrys === null) {
            $this->initEntrys();
        }

        if (isset($this->entrys[$categoryID])) {
            return $this->entrys[$categoryID];
        } else {
            return 0;
        }
    }

    /**
     * Returns unread entry count for given category.
     */
    public function getUnreadEntrys($categoryID)
    {
        if ($this->unreadEntrys === null) {
            $this->initUnreadEntrys();
        }

        if (isset($this->unreadEntrys[$categoryID])) {
            return $this->unreadEntrys[$categoryID];
        }

        return 0;
    }

    /**
     * Inits entrys.
     */
    protected function initEntrys()
    {
        $this->entrys = [];

        // no additional categories
        if (!SHOW_CATEGORY_ENABLE) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('entry.isDisabled = ?', [0]);
            $conditionBuilder->add('entry.isDeleted = ?', [0]);

            // apply language filter
            if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
                $conditionBuilder->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
            }

            $sql = "SELECT        COUNT(*) AS count, categoryID
                    FROM        show" . WCF_N . "_entry entry
                    " . $conditionBuilder . "
                    GROUP BY    entry.categoryID";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            $entrys = $statement->fetchMap('categoryID', 'count');

            $categoryToParent = [];
            foreach (CategoryHandler::getInstance()->getCategories(ShowCategory::OBJECT_TYPE_NAME) as $category) {
                if (!isset($categoryToParent[$category->parentCategoryID])) {
                    $categoryToParent[$category->parentCategoryID] = [];
                }
                $categoryToParent[$category->parentCategoryID][] = $category->categoryID;
            }

            $result = [];
            $this->countEntrys($categoryToParent, $entrys, 0, $result);
            $this->entrys = $result;
        }

        if (SHOW_CATEGORY_ENABLE) {
            $entryToCategory = [];

            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('entry.isDisabled = ?', [0]);
            $conditionBuilder->add('entry.isDeleted = ?', [0]);
            if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
                $conditionBuilder->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
            }

            $sql = "SELECT        entryID, categoryID
                    FROM        show" . WCF_N . "_entry entry
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            while ($row = $statement->fetchArray()) {
                $entryToCategory[$row['categoryID']][] = $row['entryID'];
            }

            // additional categories
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add('entry.isDisabled = ?', [0]);
            $conditionBuilder->add('entry.isDeleted = ?', [0]);
            if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
                $conditionBuilder->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
            }

            $sql = "SELECT        entry_to_category.entryID, entry_to_category.categoryID
                    FROM        show" . WCF_N . "_entry entry
                    LEFT JOIN    show" . WCF_N . "_entry_to_category entry_to_category
                    ON        (entry_to_category.entryID = entry.entryID)
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            while ($row = $statement->fetchArray()) {
                $entryToCategory[$row['categoryID']][] = $row['entryID'];
            }

            // fill parents
            foreach (CategoryHandler::getInstance()->getCategories(ShowCategory::OBJECT_TYPE_NAME) as $category) {
                if (!$category->getPermission('canViewCategory')) {
                    if (isset($entryToCategory[$category->categoryID])) {
                        unset($entryToCategory[$category->categoryID]);
                    }
                    continue;
                }
                $parents = $category->getParentCategories();
                if (!empty($parents)) {
                    foreach ($parents as $parent) {
                        if (isset($entryToCategory[$category->categoryID])) {
                            foreach ($entryToCategory[$category->categoryID] as $entryID) {
                                $entryToCategory[$parent->categoryID][] = $entryID;
                            }
                        }
                    }
                }
            }

            // cleanup and count
            $this->entrys = [];
            foreach ($entryToCategory as $categoryID => $entries) {
                $entries = \array_unique($entries);
                $this->entrys[$categoryID] = \count($entries);
            }
        }
    }

    /**
     * Inits unread entrys.
     */
    protected function initUnreadEntrys()
    {
        $this->unreadEntrys = [];

        if (WCF::getUser()->userID) {
            $conditionBuilder = new PreparedStatementConditionBuilder();
            if (SHOW_LAST_CHANGE_NEW) {
                $conditionBuilder->add('entry.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
            } else {
                $conditionBuilder->add('entry.time > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
            }
            $conditionBuilder->add('entry.isDisabled = ?', [0]);
            $conditionBuilder->add('entry.isDeleted = ?', [0]);

            // apply language filter
            if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
                $conditionBuilder->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
            }

            $conditionBuilder->add('tracked_visit.visitTime IS NULL');

            $sql = "SELECT        COUNT(*) AS count, entry.categoryID
                    FROM        show" . WCF_N . "_entry entry
                    LEFT JOIN    wcf" . WCF_N . "_tracked_visit tracked_visit
                    ON        (tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry') . " AND tracked_visit.objectID = entry.entryID AND tracked_visit.userID = " . WCF::getUser()->userID . ")
                    " . $conditionBuilder . "
                    GROUP BY    entry.categoryID";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute($conditionBuilder->getParameters());
            $unreadEntrys = $statement->fetchMap('categoryID', 'count');

            $categoryToParent = [];
            foreach (CategoryHandler::getInstance()->getCategories(ShowCategory::OBJECT_TYPE_NAME) as $category) {
                if (!isset($categoryToParent[$category->parentCategoryID])) {
                    $categoryToParent[$category->parentCategoryID] = [];
                }
                $categoryToParent[$category->parentCategoryID][] = $category->categoryID;
            }

            $result = [];
            $this->countEntrys($categoryToParent, $unreadEntrys, 0, $result);
            $this->unreadEntrys = $result;
        }
    }
}
