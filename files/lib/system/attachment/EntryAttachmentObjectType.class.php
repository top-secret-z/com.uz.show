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
namespace show\system\attachment;

use show\data\entry\Entry;
use show\data\entry\EntryList;
use wcf\system\attachment\AbstractAttachmentObjectType;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Attachment object type implementation for entrys.
 */
class EntryAttachmentObjectType extends AbstractAttachmentObjectType
{
    /**
     * @inheritDoc
     */
    public function canDelete($objectID)
    {
        if ($objectID) {
            $entry = new Entry($objectID);
            if ($entry->canEdit()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canDownload($objectID)
    {
        if ($objectID) {
            $entry = new Entry($objectID);
            if ($entry->canRead()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function canUpload($objectID, $parentObjectID = 0)
    {
        if ($objectID) {
            $entry = new Entry($objectID);
            if ($entry->canEdit()) {
                return true;
            }
        }

        return WCF::getSession()->getPermission('user.show.canAddEntry');
    }

    /**
     * @inheritDoc
     */
    public function canViewPreview($objectID)
    {
        return $this->canDownload($objectID);
    }

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs)
    {
        $entryList = new EntryList();
        $entryList->setObjectIDs(\array_unique($objectIDs));
        $entryList->readObjects();

        foreach ($entryList->getObjects() as $objectID => $object) {
            $this->cachedObjects[$objectID] = $object;
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllowedExtensions()
    {
        return ArrayUtil::trim(\explode("\n", WCF::getSession()->getPermission('user.show.allowedAttachmentExtensions')));
    }

    /**
     * @inheritDoc
     */
    public function getMaxCount()
    {
        return WCF::getSession()->getPermission('user.show.maxAttachmentCount');
    }

    /**
     * @inheritDoc
     */
    public function getMaxSize()
    {
        return WCF::getSession()->getPermission('user.show.maxAttachmentSize');
    }

    /**
     * @inheritDoc
     */
    public function setPermissions(array $attachments)
    {
        $entryIDs = [];
        foreach ($attachments as $attachment) {
            // set default permissions
            $attachment->setPermissions(['canDownload' => false, 'canViewPreview' => false]);

            if ($this->getObject($attachment->objectID) === null) {
                $entryIDs[] = $attachment->objectID;
            }
        }

        if (!empty($entryIDs)) {
            $this->cacheObjects($entryIDs);
        }

        foreach ($attachments as $attachment) {
            $entry = $this->getObject($attachment->objectID);
            if ($entry !== null) {
                if (!$entry->canRead()) {
                    continue;
                }

                $attachment->setPermissions([]);
            } elseif ($attachment->tmpHash != '' && $attachment->userID == WCF::getUser()->userID) {
                $attachment->setPermissions(['canDownload' => true, 'canViewPreview' => true]);
            }
        }
    }
}
