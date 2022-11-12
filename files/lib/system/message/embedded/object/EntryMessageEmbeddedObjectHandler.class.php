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
namespace show\system\message\embedded\object;

use show\data\entry\AccessibleEntryList;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\embedded\object\AbstractMessageEmbeddedObjectHandler;
use wcf\util\ArrayUtil;

/**
 * Message embedded object handler implementation for show entrys.
 */
class EntryMessageEmbeddedObjectHandler extends AbstractMessageEmbeddedObjectHandler
{
    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs)
    {
        $entryList = new AccessibleEntryList();
        $entryList->getConditionBuilder()->add('entry.entryID IN (?)', [$objectIDs]);
        $entryList->readObjects();

        return $entryList->getObjects();
    }

    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        if (!empty($embeddedData['entry'])) {
            $parsedEntryIDs = [];
            foreach ($embeddedData['entry'] as $attributes) {
                if (!empty($attributes[0])) {
                    $parsedEntryIDs = \array_merge($parsedEntryIDs, ArrayUtil::toIntegerArray(\explode(',', $attributes[0])));
                }
            }

            $entryIDs = \array_unique(\array_filter($parsedEntryIDs));
            if (!empty($entryIDs)) {
                $entryList = new AccessibleEntryList();
                $entryList->getConditionBuilder()->add('entry.entryID IN (?)', [$entryIDs]);
                $entryList->readObjectIDs();

                return $entryList->getObjectIDs();
            }
        }

        return [];
    }
}
