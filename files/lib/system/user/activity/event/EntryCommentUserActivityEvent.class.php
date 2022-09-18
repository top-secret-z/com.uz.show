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
namespace show\system\user\activity\event;

use show\data\entry\ViewableEntryList;
use wcf\data\comment\CommentList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * User activity event implementation for entry comments.
 */
class EntryCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $commentIDs = [];
        foreach ($events as $event) {
            $commentIDs[] = $event->objectID;
        }

        // fetch comments
        $commentList = new CommentList();
        $commentList->setObjectIDs($commentIDs);
        $commentList->readObjects();
        $comments = $commentList->getObjects();

        // fetch entrys
        $entryIDs = $entrys = [];
        foreach ($comments as $comment) {
            $entryIDs[] = $comment->objectID;
        }
        if (!empty($entryIDs)) {
            $entryList = new ViewableEntryList();
            $entryList->setObjectIDs($entryIDs);
            $entryList->readObjects();
            $entrys = $entryList->getObjects();
        }

        // set message
        foreach ($events as $event) {
            if (isset($comments[$event->objectID])) {
                $comment = $comments[$event->objectID];
                if (isset($entrys[$comment->objectID])) {
                    $entry = $entrys[$comment->objectID];

                    // check permissions
                    if (!$entry->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    // add title
                    $text = WCF::getLanguage()->getDynamicVariable('show.entry.recentActivity.entryComment', [
                        'commentID' => $comment->commentID,
                        'entry' => $entry,
                    ]);
                    $event->setTitle($text);

                    // add text
                    $event->setDescription($comment->getExcerpt());
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
