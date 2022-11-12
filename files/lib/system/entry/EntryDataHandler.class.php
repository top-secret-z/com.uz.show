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
namespace show\system\entry;

use show\data\entry\Entry;
use show\data\entry\EntryList;
use wcf\system\SingletonFactory;

/**
 * Caches entry objects for entry-related user notifications.
 */
class EntryDataHandler extends SingletonFactory
{
    /**
     * list of cached entrys
     */
    protected $entryIDs = [];

    protected $entrys = [];

    /**
     * Caches an entry id.
     */
    public function cacheEntryID($entryID)
    {
        if (!\in_array($entryID, $this->entryIDs)) {
            $this->entryIDs[] = $entryID;
        }
    }

    /**
     * Returns the entry with the given id.
     */
    public function getEntry($entryID)
    {
        if (!empty($this->entryIDs)) {
            $this->entryIDs = \array_diff($this->entryIDs, \array_keys($this->entrys));

            if (!empty($this->entryIDs)) {
                $entryList = new EntryList();
                $entryList->setObjectIDs($this->entryIDs);
                $entryList->readObjects();
                $this->entrys += $entryList->getObjects();
                $this->entryIDs = [];
            }
        }

        if (isset($this->entrys[$entryID])) {
            return $this->entrys[$entryID];
        }

        return null;
    }
}
