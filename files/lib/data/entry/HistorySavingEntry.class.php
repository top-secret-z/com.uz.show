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

use show\system\SHOWCore;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\TUserContent;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\WCF;

/**
 * History saving point implementation for entrys.
 */
class HistorySavingEntry extends DatabaseObjectDecorator implements IHistorySavingObject
{
    use TUserContent;

    /**
     * last edit data
     */
    public $reason = '';

    public $time = 0;

    public $userID = 0;

    public $username = '';

    /**
     * @inheritDoc
     */
    protected static $baseClass = Entry::class;

    /**
     * @inheritDoc
     */
    public function __construct(DatabaseObject $object)
    {
        parent::__construct($object);

        // fetch the data of the latest edit
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.modifiableContent', 'com.uz.show.entry');

        $sql = "SELECT    *
                FROM        wcf" . WCF_N . "_modification_log
                WHERE        objectTypeID = ? AND objectID = ? AND action = ?
                ORDER BY     time DESC";
        $statement = WCF::getDB()->prepareStatement($sql, 1);
        $statement->execute([$objectTypeID, $this->getDecoratedObject()->entryID, 'edit']);
        $row = $statement->fetchSingleRow();

        if ($row) {
            $this->userID = $row['userID'];
            $this->username = $row['username'];
            $this->time = $row['time'];
            $additionalData = @\unserialize($row['additionalData']);
            if (isset($additionalData['reason'])) {
                $this->reason = $additionalData['reason'];
            } else {
                $this->reason = '';
            }
        } else {
            $this->userID = $this->getDecoratedObject()->getUserID();
            $this->username = $this->getDecoratedObject()->getUsername();
            $this->time = $this->getDecoratedObject()->getTime();
            $this->reason = '';
        }
    }

    /**
     * @inheritDoc
     */
    public function getEditReason()
    {
        return $this->reason;
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return $this->getDecoratedObject()->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getDecoratedObject()->getMessage();
    }

    /**
     * @inheritDoc
     */
    public function setLocation()
    {
        if (!SHOW_CATEGORY_ENABLE) {
            SHOWCore::getInstance()->setLocation($this->getCategory()->getParentCategories(), $this->getCategory(), $this->getDecoratedObject());
        } else {
            SHOWCore::getInstance()->setLocation();
        }
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getDecoratedObject()->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function revertVersion(EditHistoryEntry $edit)
    {
        $entryAction = new EntryAction([$this->getDecoratedObject()], 'update', [
            'isEdit' => true,
            'data' => [
                'message' => $edit->message,
            ],
            'editReason' => WCF::getLanguage()->getDynamicVariable('wcf.edit.reverted', ['edit' => $edit]),
        ]);
        $entryAction->executeAction();
    }
}
