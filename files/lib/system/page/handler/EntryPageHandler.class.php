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

use show\data\entry\ViewableEntryList;
use show\system\cache\runtime\ViewableEntryRuntimeCache;
use wcf\data\page\Page;
use wcf\data\user\online\UserOnline;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TOnlineLocationPageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Menu page handler for the entry page.
 */
class EntryPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TOnlineLocationPageHandler;

    /**
     * @inheritDoc
     */
    public function getLink($objectID)
    {
        return ViewableEntryRuntimeCache::getInstance()->getObject($objectID)->getLink();
    }

    /**
     * @inheritDoc
     */
    public function getOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID === null) {
            return '';
        }

        $entry = ViewableEntryRuntimeCache::getInstance()->getObject($user->pageObjectID);
        if ($entry === null || !$entry->canRead()) {
            return '';
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.page.onlineLocation.' . $page->identifier, ['entry' => $entry]);
    }

    /**
     * @inheritDoc
     */
    public function isValid($objectID)
    {
        return ViewableEntryRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        return ViewableEntryRuntimeCache::getInstance()->getObject($objectID)->canRead();
    }

    /**
     * @inheritDoc
     */
    public function lookup($searchString)
    {
        $entryList = new ViewableEntryList();
        $entryList->getConditionBuilder()->add('entry.subject LIKE ?', ['%' . $searchString . '%']);
        $entryList->sqlLimit = 10;
        $entryList->sqlOrderBy = 'entry.subject';
        $entryList->readObjects();

        $results = [];
        foreach ($entryList->getObjects() as $entry) {
            $results[] = [
                'description' => StringUtil::encodeHTML($entry->getTeaser()),
                'image' => $entry->getIconTag(48),
                'link' => $entry->getLink(),
                'objectID' => $entry->entryID,
                'title' => $entry->getTitle(),
            ];
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function prepareOnlineLocation(Page $page, UserOnline $user)
    {
        if ($user->pageObjectID !== null) {
            ViewableEntryRuntimeCache::getInstance()->cacheObjectID($user->pageObjectID);
        }
    }
}
