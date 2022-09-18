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
namespace show\system\user\notification\event;

use show\system\entry\EntryDataHandler;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\email\Email;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for entry comment responses.
 */
class EntryCommentResponseUserNotificationEvent extends AbstractSharedUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    protected $stackable = true;

    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID'])->canRead();
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        $comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
        $entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);

        $messageID = '<com.uz.show.entry.comment/' . $comment->commentID . '@' . Email::getHost() . '>';

        return [
            'template' => 'email_notification_commentResponse',
            'application' => 'wcf',
            'in-reply-to' => [$messageID],
            'references' => [$messageID],
            'variables' => [
                'commentID' => $this->getUserNotificationObject()->commentID,
                'entry' => $entry,
                'responseID' => $this->getUserNotificationObject()->responseID,
                'languageVariablePrefix' => 'show.entry.commentResponse.notification',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getEventHash()
    {
        return \sha1($this->eventID . '-' . $this->getUserNotificationObject()->commentID);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        $entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);

        $authors = $this->getAuthors();
        if (\count($authors) > 1) {
            if (isset($authors[0])) {
                unset($authors[0]);
            }
            $count = \count($authors);

            return $this->getLanguage()->getDynamicVariable('show.entry.commentResponse.notification.message.stacked', [
                'authors' => \array_values($authors),
                'commentID' => $this->getUserNotificationObject()->commentID,
                'count' => $count,
                'entry' => $entry,
                'others' => $count - 1,
                'guestTimesTriggered' => $this->notification->guestTimesTriggered,
            ]);
        }

        return $this->getLanguage()->getDynamicVariable('show.entry.commentResponse.notification.message', [
            'entry' => $entry,
            'author' => $this->author,
            'commentID' => $this->getUserNotificationObject()->commentID,
            'responseID' => $this->getUserNotificationObject()->responseID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        $count = \count($this->getAuthors());
        if ($count > 1) {
            return $this->getLanguage()->getDynamicVariable('show.entry.commentResponse.notification.title.stacked', [
                'count' => $count,
                'timesTriggered' => $this->notification->timesTriggered,
            ]);
        }

        return $this->getLanguage()->get('show.entry.commentResponse.notification.title');
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        $entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);

        return LinkHandler::getInstance()->getLink('Entry', [
            'application' => 'show',
            'object' => $entry,
        ], '#comments/comment' . $this->getUserNotificationObject()->commentID);
    }

    /**
     * @inheritDoc
     */
    protected function prepare()
    {
        EntryDataHandler::getInstance()->cacheEntryID($this->additionalData['objectID']);
    }
}
