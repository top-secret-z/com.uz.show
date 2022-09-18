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
namespace show\system\bbcode;

use wcf\system\bbcode\AbstractBBCode;
use wcf\system\bbcode\BBCodeParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Parses the [entry] bbcode tag.
 */
class EntryBBCode extends AbstractBBCode
{
    /**
     * @inheritDoc
     */
    public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser)
    {
        $entryIDs = [];
        if (isset($openingTag['attributes'][0])) {
            $entryIDs = \array_unique(ArrayUtil::toIntegerArray(\explode(',', $openingTag['attributes'][0])));
        }

        $entrys = [];
        foreach ($entryIDs as $entryID) {
            $entry = MessageEmbeddedObjectManager::getInstance()->getObject('com.uz.show.entry', $entryID);
            if ($entry !== null && $entry->canRead()) {
                $entrys[] = $entry;
            }
        }

        if (!empty($entrys)) {
            if ($parser->getOutputType() == 'text/html') {
                return WCF::getTPL()->fetch('entryBBCode', 'show', [
                    'entrys' => $entrys,
                    'titleHash' => \substr(StringUtil::getRandomID(), 0, 8),
                ], true);
            }

            $result = '';
            foreach ($entrys as $entry) {
                if (!empty($result)) {
                    $result .= ' ';
                }
                $result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Entry', [
                    'application' => 'show',
                    'object' => $entry,
                ]));
            }

            return $result;
        }

        if (!empty($entryIDs)) {
            $result = '';
            foreach ($entryIDs as $entryID) {
                if ($entryID) {
                    if (!empty($result)) {
                        $result .= ' ';
                    }
                    $result .= StringUtil::getAnchorTag(LinkHandler::getInstance()->getLink('Entry', [
                        'application' => 'show',
                        'id' => $entryID,
                    ]));
                }
            }

            return $result;
        }
    }
}
