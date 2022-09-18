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
namespace show\system\menu\user\profile\content;

use show\data\entry\AccessibleEntryList;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\menu\user\profile\content\IUserProfileMenuContent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile show content.
 */
class ShowUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent
{
    /**
     * list of accessible entries
     */
    protected $entryLists = [];

    /**
     * @inheritdoc
     */
    public function getContent($userID)
    {
        return WCF::getTPL()->fetch('userProfileEntrys', 'show', [
            'entryList' => $this->getEntryList($userID),
            'user' => UserProfileRuntimeCache::getInstance()->getObject($userID),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isVisible($userID)
    {
        $user = UserProfileRuntimeCache::getInstance()->getObject($userID);
        if ($user !== null && $user->showEntrys && \count($this->getEntryList($userID))) {
            return true;
        }

        return false;
    }

    /**
     * Returns the list with all entries created by the user
     */
    protected function getEntryList($userID)
    {
        if (!isset($this->entryLists[$userID])) {
            $entryList = new AccessibleEntryList();
            $entryList->getConditionBuilder()->add('entry.userID = ?', [$userID]);
            $entryList->sqlLimit = 8;
            $entryList->readObjects();

            $this->entryLists[$userID] = $entryList;
        }

        return $this->entryLists[$userID];
    }
}
