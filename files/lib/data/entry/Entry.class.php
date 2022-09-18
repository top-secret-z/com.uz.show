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

use LogicException;
use show\data\category\ShowCategory;
use wcf\data\attachment\GroupedAttachmentList;
use wcf\data\DatabaseObject;
use wcf\data\IMessage;
use wcf\data\TUserContent;
use wcf\system\bbcode\AttachmentBBCode;
use wcf\system\category\CategoryHandler;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;
use wcf\util\UserUtil;

/**
 * Represents an entry.
 */
class Entry extends DatabaseObject implements IMessage, IRouteController
{
    use TUserContent;

    /**
     * entry's category
     */
    protected $category;

    /**
     * true if embedded objects have already been loaded
     */
    protected $embeddedObjectsLoaded = false;

    protected $embeddedObjectsLoaded2 = false;

    protected $embeddedObjectsLoaded3 = false;

    protected $embeddedObjectsLoaded4 = false;

    protected $embeddedObjectsLoaded5 = false;

    /**
     * entry option values
     */
    protected $optionValues;

    /**
     * dimensions of entry icons (square)
     */
    const ICON_SIZE = 144;

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->getFormattedMessage();
    }

    /**
     * Returns and assigns embedded attachments.
     */
    public function getAttachments()
    {
        if (MODULE_ATTACHMENT && $this->attachments) {
            $attachmentList = new GroupedAttachmentList('com.uz.show.entry');
            $attachmentList->getConditionBuilder()->add('attachment.objectID IN (?)', [$this->entryID]);
            $attachmentList->readObjects();
            $attachmentList->setPermissions([]);

            // set embedded attachments
            AttachmentBBCode::setAttachmentList($attachmentList);

            return $attachmentList;
        }

        return null;
    }

    /**
     * Returns the category of the entry.
     */
    public function getCategory()
    {
        if ($this->category === null && $this->categoryID) {
            $this->category = ShowCategory::getCategory($this->categoryID);
        }

        return $this->category;
    }

    /**
     * Returns the additional categories of the entry; includes main category
     */
    public function getCategories()
    {
        if ($this->categories === null) {
            $this->categories = [];

            if (!empty($this->categoryIDs)) {
                foreach ($this->categoryIDs as $categoryID) {
                    $this->categories[$categoryID] = new ShowCategory(CategoryHandler::getInstance()->getCategory($categoryID));
                }
            } else {
                $sql = "SELECT    categoryID
                        FROM    show" . WCF_N . "_entry_to_category
                        WHERE    entryID = ?";
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute([$this->entryID]);
                while ($row = $statement->fetchArray()) {
                    $this->categories[$row['categoryID']] = new ShowCategory(CategoryHandler::getInstance()->getCategory($row['categoryID']));
                }
            }
        }

        // main category is not included after update or when additional categories are disabled.
        if (!isset($this->categories[$this->categoryID])) {
            $this->categories[$this->categoryID] = ShowCategory::getCategory($this->categoryID);
        }

        return $this->categories;
    }

    /**
     * @inheritDoc
     */
    public function getExcerpt($maxLength = 255)
    {
        return StringUtil::truncateHTML($this->getSimplifiedFormattedMessage(), $maxLength);
    }

    /**
     * @inheritDoc
     */
    public function getFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->process($this->getMessage(), 'com.uz.show.entry', $this->entryID);

        return $processor->getHtml();
    }

    /**
     * get additional texts
     */
    public function getFormattedMessage2()
    {
        $this->loadEmbeddedObjects2();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->process($this->text2, 'com.uz.show.entry.text2', $this->entryID);

        return $processor->getHtml();
    }

    /**
     * get additional texts
     */
    public function getFormattedMessage3()
    {
        $this->loadEmbeddedObjects3();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->process($this->text3, 'com.uz.show.entry.text3', $this->entryID);

        return $processor->getHtml();
    }

    /**
     * get additional texts
     */
    public function getFormattedMessage4()
    {
        $this->loadEmbeddedObjects4();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->process($this->text4, 'com.uz.show.entry.text4', $this->entryID);

        return $processor->getHtml();
    }

    /**
     * get additional texts
     */
    public function getFormattedMessage5()
    {
        $this->loadEmbeddedObjects5();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->process($this->text5, 'com.uz.show.entry.text5', $this->entryID);

        return $processor->getHtml();
    }

    /**
     * Returns the location of the entry icon.
     */
    public function getIconLocation()
    {
        if ($this->iconHash) {
            return SHOW_DIR . 'images/entry/' . \substr($this->iconHash, 0, 2) . '/' . $this->entryID . '.' . $this->iconExtension;
        }

        return '';
    }

    /**
     * Returns the url of the entry icon.
     */
    public function getIconURL()
    {
        if ($this->iconHash) {
            return WCF::getPath('show') . 'images/entry/' . \substr($this->iconHash, 0, 2) . '/' . $this->entryID . '.' . $this->iconExtension;
        }

        return '';
    }

    /**
     * Returns entry's ip address.
     */
    public function getIpAddress()
    {
        if ($this->ipAddress) {
            return UserUtil::convertIPv6To4($this->ipAddress);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getLink()
    {
        return LinkHandler::getInstance()->getLink('Entry', [
            'application' => 'show',
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns a version of this message optimized for use in emails.
     */
    public function getMailText($mimeType = 'text/plain')
    {
        switch ($mimeType) {
            case 'text/plain':
                $processor = new HtmlOutputProcessor();
                $processor->setOutputType('text/plain');
                $processor->process($this->getMessage(), 'com.uz.show.entry', $this->entryID);

                return $processor->getHtml();
            case 'text/html':
                return $this->getSimplifiedFormattedMessage();
        }

        throw new LogicException('Unreachable');
    }

    /**
     * Returns a specific entry option value.
     */
    public function getOptionValue($optionID)
    {
        if ($this->optionValues === null) {
            $this->optionValues = [];
            $sql = "SELECT    optionID, optionValue
                    FROM    show" . WCF_N . "_entry_option_value
                    WHERE    entryID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->entryID]);

            $this->optionValues = $statement->fetchMap('optionID', 'optionValue');
        }

        if (isset($this->optionValues[$optionID])) {
            return $this->optionValues[$optionID];
        }

        return '';
    }

    /**
     * Returns a simplified version of the formatted message.
     */
    public function getSimplifiedFormattedMessage()
    {
        $this->loadEmbeddedObjects();

        // parse and return message
        $processor = new HtmlOutputProcessor();
        $processor->setOutputType('text/simplified-html');
        $processor->process($this->getMessage(), 'com.uz.show.entry', $this->entryID);

        return $processor->getHtml();
    }

    /**
     * Returns the subject of this entry.
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Returns the teaser of this entry.
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->getSubject();
    }

    /**
     * Returns true if the active user can delete this entry.
     */
    public function canDelete()
    {
        if (WCF::getSession()->getPermission('mod.show.canDeleteEntry')) {
            return true;
        }

        if ($this->isOwner() && WCF::getSession()->getPermission('user.show.canDeleteEntry')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the active user can edit this entry.
     */
    public function canEdit()
    {
        // check mod permissions
        if (WCF::getSession()->getPermission('mod.show.canEditEntry') || WCF::getSession()->getPermission('user.show.canEditEntryOfOthers')) {
            return true;
        }

        // check user permissions
        if ($this->isAuthor() && WCF::getSession()->getPermission('user.show.canEditEntry')) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the active user can read this entry.
     */
    public function canRead()
    {
        if ($this->isDeleted && !WCF::getSession()->getPermission('mod.show.canViewDeletedEntry')) {
            return false;
        }

        if ($this->isDisabled && !WCF::getSession()->getPermission('mod.show.canModerateEntry') && !$this->isAuthor()) {
            return false;
        }

        if ($this->getCategory()) {
            return $this->getCategory()->isAccessible();
        }

        return WCF::getSession()->getPermission('user.show.canViewEntry');
    }

    /**
     * Returns true if this entry has got old versions in edit history.
     */
    public function hasOldVersions()
    {
        if (!MODULE_EDIT_HISTORY) {
            return false;
        }
        if (EDIT_HISTORY_EXPIRATION == 0) {
            return $this->lastVersionTime > 0;
        }

        return $this->lastVersionTime > (TIME_NOW - EDIT_HISTORY_EXPIRATION * 86400);
    }

    /**
     * Returns true if the active user is an author of the entry.
     */
    public function isAuthor()
    {
        return $this->isOwner();
    }

    /**
     * Returns true if the active user is the owner of the entry.
     */
    public function isOwner()
    {
        return WCF::getUser()->userID && $this->userID == WCF::getUser()->userID;
    }

    /**
     * @inheritDoc
     */
    public function isVisible()
    {
        return $this->canRead();
    }

    /**
     * Loads the embedded objects.
     */
    public function loadEmbeddedObjects()
    {
        if ($this->hasEmbeddedObjects && !$this->embeddedObjectsLoaded) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.show.entry', [$this->entryID]);
            $this->embeddedObjectsLoaded = true;
        }
    }

    public function loadEmbeddedObjects2()
    {
        if ($this->hasEmbeddedObjects2 && !$this->embeddedObjectsLoaded2) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.show.entry.text2', [$this->entryID]);
            $this->embeddedObjectsLoaded2 = true;
        }
    }

    public function loadEmbeddedObjects3()
    {
        if ($this->hasEmbeddedObjects3 && !$this->embeddedObjectsLoaded3) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.show.entry.text3', [$this->entryID]);
            $this->embeddedObjectsLoaded3 = true;
        }
    }

    public function loadEmbeddedObjects4()
    {
        if ($this->hasEmbeddedObjects4 && !$this->embeddedObjectsLoaded4) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.show.entry.text4', [$this->entryID]);
            $this->embeddedObjectsLoaded4 = true;
        }
    }

    public function loadEmbeddedObjects5()
    {
        if ($this->hasEmbeddedObjects5 && !$this->embeddedObjectsLoaded5) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.show.entry.text5', [$this->entryID]);
            $this->embeddedObjectsLoaded5 = true;
        }
    }

    /**
     * Returns the url of the default image
     */
    public function getDefaultImageURL()
    {
        return WCF::getPath('show') . 'images/default.jpg';
    }
}
