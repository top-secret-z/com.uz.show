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

use show\data\modification\log\EntryListModificationLogList;
use show\system\label\object\EntryLabelObjectHandler;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\reaction\ReactionHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of viewable entrys.
 */
class ViewableEntryList extends EntryList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableEntry::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'entry.lastChangeTime DESC';

    /**
     * load delete notes
     */
    public $loadDeleteNote = true;

    /**
     * list of modification log entries for entrys
     */
    public $logList;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        // get avatars
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "user_avatar.*, user_table.*";
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_user user_table ON (user_table.userID = entry.userID)";
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_user_avatar user_avatar ON (user_avatar.avatarID = user_table.avatarID)";

        // last visit time
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= 'tracked_visit.visitTime';
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry') . " AND tracked_visit.objectID = entry.entryID AND tracked_visit.userID = " . WCF::getUser()->userID . ")";

        // subscriptions
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= 'user_object_watch.watchID, user_object_watch.notification';
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_user_object_watch user_object_watch ON (user_object_watch.objectTypeID = " . ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.user.objectWatch', 'com.uz.show.entry')->objectTypeID . " AND user_object_watch.userID = " . WCF::getUser()->userID . " AND user_object_watch.objectID = entry.entryID)";

        // reactions
        if (!empty($this->sqlSelects)) {
            $this->sqlSelects .= ',';
        }
        $this->sqlSelects .= "like_object.cachedReactions";
        $this->sqlJoins .= " LEFT JOIN wcf" . WCF_N . "_like_object like_object ON (like_object.objectTypeID = " . ReactionHandler::getInstance()->getObjectType('com.uz.show.likeableEntry')->objectTypeID . " AND like_object.objectID = entry.entryID)";

        if (!WCF::getSession()->getPermission('mod.show.canViewDeletedEntry')) {
            $this->loadDeleteNote = false;
        }
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        // assigned labels
        $entryIDs = [];
        foreach ($this->objects as $entry) {
            if ($entry->hasLabels) {
                $entryIDs[] = $entry->entryID;
            }
        }

        if (!empty($entryIDs)) {
            $assignedLabels = EntryLabelObjectHandler::getInstance()->getAssignedLabels($entryIDs);
            foreach ($assignedLabels as $entryID => $labels) {
                foreach ($labels as $label) {
                    $this->objects[$entryID]->addLabel($label);
                }
            }
        }

        // deletion / log data
        if ($this->loadDeleteNote) {
            $objectIDs = [];
            foreach ($this->objects as $object) {
                if ($object->isDeleted) {
                    $objectIDs[] = $object->entryID;
                }
            }

            if (!empty($objectIDs)) {
                $this->logList = new EntryListModificationLogList();
                $this->logList->setEntryData($objectIDs, 'trash');
                $this->logList->readObjects();

                foreach ($this->logList as $logEntry) {
                    foreach ($this->objects as $object) {
                        if ($object->entryID == $logEntry->objectID) {
                            $object->setLogEntry($logEntry);
                        }
                    }
                }
            }
        }
    }
}
