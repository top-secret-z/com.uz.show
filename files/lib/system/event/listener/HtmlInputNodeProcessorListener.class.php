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
namespace show\system\event\listener;

use show\data\entry\AccessibleEntryList;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\event\listener\AbstractHtmlInputNodeProcessorListener;
use wcf\system\request\LinkHandler;

/**
 * Parses URLs of show entries.
 */
class HtmlInputNodeProcessorListener extends AbstractHtmlInputNodeProcessorListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        // replace entry links
        if (BBCodeHandler::getInstance()->isAvailableBBCode('entry')) {
            $regex = $this->getRegexFromLink(LinkHandler::getInstance()->getLink('Entry', [
                'application' => 'show',
                'forceFrontend' => true,
            ]), 'overview');
            $entryIDs = $this->getObjectIDs($eventObj, $regex);

            if (!empty($entryIDs)) {
                $entryList = new AccessibleEntryList();
                $entryList->getConditionBuilder()->add('entry.entryID IN (?)', [\array_unique($entryIDs)]);
                $entryList->readObjects();

                $this->replaceLinksWithBBCode($eventObj, $regex, $entryList->getObjects(), 'entry');
            }
        }
    }
}
