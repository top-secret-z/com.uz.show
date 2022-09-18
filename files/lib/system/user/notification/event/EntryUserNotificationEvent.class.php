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

use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractUserNotificationEvent;

/**
 * Notification event for new entrys.
 */
class EntryUserNotificationEvent extends AbstractUserNotificationEvent
{
    /**
     * @inheritDoc
     */
    public function checkAccess()
    {
        return $this->getUserNotificationObject()->canRead();
    }

    /**
     * @inheritDoc
     */
    public function getEmailMessage($notificationType = 'instant')
    {
        return [
            'message-id' => 'com.uz.show.entry/' . $this->getUserNotificationObject()->entryID,
            'template' => 'email_notification_entry',
            'application' => 'show',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Entry', [
            'application' => 'show',
            'object' => $this->getUserNotificationObject(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->getLanguage()->getDynamicVariable('show.entry.notification.message', [
            'entry' => $this->userNotificationObject,
            'author' => $this->author,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getLanguage()->get('show.entry.notification.title');
    }
}
