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

use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Object type provider for entrys.
 */
class LikeableEntryProvider extends EntryProvider implements ILikeObjectTypeProvider, IViewableLikeProvider
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = LikeableEntry::class;

    /**
     * @inheritDoc
     */
    public function checkPermissions(ILikeObject $object)
    {
        return $object->entryID && $object->canRead();
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $likes)
    {
        $entryIDs = [];
        foreach ($likes as $like) {
            $entryIDs[] = $like->objectID;
        }

        // get entrys
        $entryList = new ViewableEntryList();
        $entryList->setObjectIDs($entryIDs);
        $entryList->readObjects();
        $entrys = $entryList->getObjects();

        // set message
        foreach ($likes as $like) {
            if (isset($entrys[$like->objectID])) {
                $entry = $entrys[$like->objectID];

                // check permissions
                if (!$entry->canRead()) {
                    continue;
                }

                $like->setIsAccessible();

                // short output
                $text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.show.likeableEntry', ['entry' => $entry, 'like' => $like]);
                $like->setTitle($text);

                // output
                $like->setDescription($entry->getExcerpt());
            }
        }
    }
}
