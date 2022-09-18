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
use wcf\data\comment\response\CommentResponseList;
use wcf\data\user\UserList;
use wcf\system\SingletonFactory;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\WCF;

/**
 * User activity event implementation for entry comment responses.
 */
class EntryCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $responseIDs = [];
        foreach ($events as $event) {
            $responseIDs[] = $event->objectID;
        }

        // fetch responses
        $responseList = new CommentResponseList();
        $responseList->setObjectIDs($responseIDs);
        $responseList->readObjects();
        $responses = $responseList->getObjects();

        // fetch comments
        $commentIDs = $comments = [];
        foreach ($responses as $response) {
            $commentIDs[] = $response->commentID;
        }
        if (!empty($commentIDs)) {
            $commentList = new CommentList();
            $commentList->setObjectIDs($commentIDs);
            $commentList->readObjects();
            $comments = $commentList->getObjects();
        }

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

        // fetch users
        $userIDs = $user = [];
        foreach ($comments as $comment) {
            $userIDs[] = $comment->userID;
        }
        if (!empty($userIDs)) {
            $userList = new UserList();
            $userList->setObjectIDs($userIDs);
            $userList->readObjects();
            $users = $userList->getObjects();
        }

        // set message
        foreach ($events as $event) {
            if (isset($responses[$event->objectID])) {
                $response = $responses[$event->objectID];
                $comment = $comments[$response->commentID];
                if (isset($entrys[$comment->objectID]) && isset($users[$comment->userID])) {
                    $entry = $entrys[$comment->objectID];

                    // check permissions
                    if (!$entry->canRead()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    // title
                    $text = WCF::getLanguage()->getDynamicVariable('show.entry.recentActivity.entryCommentResponse', [
                        'commentAuthor' => $users[$comment->userID],
                        'commentID' => $comment->commentID,
                        'responseID' => $response->responseID,
                        'entry' => $entry,
                    ]);
                    $event->setTitle($text);

                    // description
                    $event->setDescription($response->getExcerpt());
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
