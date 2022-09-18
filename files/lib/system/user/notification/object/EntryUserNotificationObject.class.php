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
namespace show\system\user\notification\object;

use show\data\entry\Entry;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Represents an entry as a notification object.
 */
class EntryUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Entry::class;

    /**
     * @inheritDoc
     */
    public function getAuthorID()
    {
        return $this->userID;
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
        return $this->getLink();
    }
}
