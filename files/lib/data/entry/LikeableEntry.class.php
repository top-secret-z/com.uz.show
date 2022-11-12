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

use wcf\data\like\Like;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\reaction\object\IReactionObject;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for entrys.
 */
class LikeableEntry extends AbstractLikeObject implements IReactionObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Entry::class;

    /**
     * @inheritDoc
     */
    public function getLanguageID()
    {
        return $this->getDecoratedObject()->languageID;
    }

    /**
     * @inheritDoc
     */
    public function getObjectID()
    {
        return $this->entryID;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getSubject();
    }

    /**
     * @inheritDoc
     */
    public function getURL()
    {
        return LinkHandler::getInstance()->getLink('Entry', [
            'application' => 'show',
            'object' => $this->getDecoratedObject(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getUserID()
    {
        return $this->userID;
    }

    /**
     * @inheritDoc
     */
    public function sendNotification(Like $like)
    {
        if ($this->getDecoratedObject()->userID != WCF::getUser()->userID) {
            $notificationObject = new LikeUserNotificationObject($like);
            UserNotificationHandler::getInstance()->fireEvent(
                'like',
                'com.uz.show.likeableEntry.notification',
                $notificationObject,
                [$this->getDecoratedObject()->userID],
                ['objectID' => $this->getDecoratedObject()->entryID]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function updateLikeCounter($cumulativeLikes)
    {
        $editor = new EntryEditor($this->getDecoratedObject());
        $editor->update(['cumulativeLikes' => $cumulativeLikes]);
    }
}
