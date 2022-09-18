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
namespace show\data\modification\log;

use show\data\entry\Entry;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\WCF;

/**
 * Provides a viewable entry modification log.
 */
class ViewableEntryModificationLog extends DatabaseObjectDecorator implements IViewableModificationLog
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = ModificationLog::class;

    /**
     * Entry this modification log belongs to
     */
    protected $entry;

    /**
     * user profile object
     */
    protected $userProfile;

    /**
     * Returns readable representation of current log entry.
     */
    public function __toString()
    {
        return WCF::getLanguage()->getDynamicVariable('show.entry.log.entry.' . $this->action, ['additionalData' => $this->additionalData]);
    }

    /**
     * Returns the user profile object.
     */
    public function getUserProfile()
    {
        if ($this->userProfile === null) {
            $this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
        }

        return $this->userProfile;
    }

    /**
     * Sets the entry this modification log belongs to.
     */
    public function setEntry(Entry $entry)
    {
        if ($entry->entryID == $this->objectID) {
            $this->entry = $entry;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAffectedObject()
    {
        return $this->entry;
    }
}
