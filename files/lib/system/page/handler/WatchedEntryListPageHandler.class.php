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
namespace show\system\page\handler;

use show\data\category\ShowCategory;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Menu page handler for the watched entrys page.
 */
class WatchedEntryListPageHandler extends AbstractMenuPageHandler
{
    /**
     * number of unread entrys
     */
    protected $notifications;

    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null)
    {
        if ($this->notifications === null) {
            $this->notifications = 0;

            if (WCF::getUser()->userID) {
                $data = UserStorageHandler::getInstance()->getField('showUnreadWatchedEntrys');

                // cache does not exist or is outdated
                if ($data === null) {
                    $categoryIDs = ShowCategory::getAccessibleCategoryIDs();
                    if (!empty($categoryIDs)) {
                        $objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.entry');

                        $conditionBuilder = new PreparedStatementConditionBuilder();
                        $conditionBuilder->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
                        $conditionBuilder->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
                        $conditionBuilder->add('entry.categoryID IN (?)', [$categoryIDs]);
                        $conditionBuilder->add('entry.isDeleted = 0 AND entry.isDisabled = 0');
                        if (SHOW_LAST_CHANGE_NEW) {
                            $conditionBuilder->add('entry.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
                            $conditionBuilder->add('(entry.lastChangeTime > tracked_entry_visit.visitTime OR tracked_entry_visit.visitTime IS NULL)');
                        } else {
                            $conditionBuilder->add('entry.time > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
                            $conditionBuilder->add('(entry.time > tracked_entry_visit.visitTime OR tracked_entry_visit.visitTime IS NULL)');
                        }

                        $sql = "SELECT        COUNT(*)
                                FROM        wcf" . WCF_N . "_user_object_watch user_object_watch
                                LEFT JOIN    show" . WCF_N . "_entry entry
                                ON            (entry.entryID = user_object_watch.objectID)
                                LEFT JOIN    wcf" . WCF_N . "_tracked_visit tracked_entry_visit
                                ON            (tracked_entry_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry') . " AND tracked_entry_visit.objectID = entry.entryID AND tracked_entry_visit.userID = " . WCF::getUser()->userID . ")
                                " . $conditionBuilder;
                        $statement = WCF::getDB()->prepareStatement($sql);
                        $statement->execute($conditionBuilder->getParameters());
                        $this->notifications = $statement->fetchSingleColumn();
                    }

                    // update storage data
                    UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'showUnreadWatchedEntrys', $this->notifications);
                } else {
                    $this->notifications = $data;
                }
            }
        }

        return $this->notifications;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        $count = 0;
        if (WCF::getUser()->userID) {
            $data = UserStorageHandler::getInstance()->getField('showWatchedEntrys');

            // cache does not exist or is outdated
            if ($data === null) {
                $categoryIDs = ShowCategory::getAccessibleCategoryIDs();
                if (!empty($categoryIDs)) {
                    $objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.entry');

                    $conditionBuilder = new PreparedStatementConditionBuilder();
                    $conditionBuilder->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
                    $conditionBuilder->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
                    $conditionBuilder->add('entry.categoryID IN (?)', [$categoryIDs]);
                    $conditionBuilder->add('entry.isDeleted = 0 AND entry.isDisabled = 0');

                    $sql = "SELECT        COUNT(*)
                            FROM        wcf" . WCF_N . "_user_object_watch user_object_watch
                            LEFT JOIN    show" . WCF_N . "_entry entry
                            ON            (entry.entryID = user_object_watch.objectID)
                            " . $conditionBuilder;
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute($conditionBuilder->getParameters());
                    $count = $statement->fetchSingleColumn();
                }

                // update storage data
                UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'showWatchedEntrys', $count);
            } else {
                $count = $data;
            }
        }

        return $count != 0;
    }
}
