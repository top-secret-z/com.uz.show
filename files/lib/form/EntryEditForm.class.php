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
namespace show\form;

use RuntimeException;
use show\data\category\ShowCategory;
use show\data\entry\Entry;
use show\data\entry\EntryAction;
use show\system\label\object\EntryLabelObjectHandler;
use show\system\SHOWCore;
use wcf\form\MessageForm;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\censorship\Censorship;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the entry edit form.
 */
class EntryEditForm extends EntryAddForm
{
    /**
     * entry data
     */
    public $entryID = 0;

    public $entry;

    public $editReason = '';

    /**
     * html
     */
    public $htmlInputProcessor2;

    public $htmlInputProcessor3;

    public $htmlInputProcessor4;

    public $htmlInputProcessor5;

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        // display tabs if enabled AND either wysiwyg, option or images
        $tabs[1] = 1;
        $tabs[2] = $tabs[3] = $tabs[4] = $tabs[5] = 0;

        $tab1Options = 0;

        $options = $this->optionHandler->getOptions();
        if (!empty($options)) {
            foreach ($options as $data) {
                $option = $data['object'];
                $tabs[$option->tab] = 1;
                if ($option->tab == 1) {
                    $tab1Options = 1;
                }
            }
        }

        if (SHOW_TAB2_ENABLE && (SHOW_TAB2_WYSIWYG || SHOW_IMAGES_TAB == 2)) {
            $tabs[2] = 1;
        }
        if (SHOW_TAB3_ENABLE && (SHOW_TAB3_WYSIWYG || SHOW_IMAGES_TAB == 3)) {
            $tabs[3] = 1;
        }
        if (SHOW_TAB4_ENABLE && (SHOW_TAB4_WYSIWYG || SHOW_IMAGES_TAB == 4)) {
            $tabs[4] = 1;
        }
        if (SHOW_TAB5_ENABLE && (SHOW_TAB5_WYSIWYG || SHOW_IMAGES_TAB == 5)) {
            $tabs[5] = 1;
        }

        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'edit',
            'editReason' => $this->editReason,
            'entryID' => $this->entryID,
            'entry' => $this->entry,
            'tabs' => $tabs,
            'tab1Options' => $tab1Options,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->entryID = \intval($_REQUEST['id']);
        }
        $this->entry = new Entry($this->entryID);
        if (!$this->entry->entryID) {
            throw new IllegalLinkException();
        }

        parent::readParameters();

        $this->entryOwnerID = $this->entry->userID;

        // set attachment object id
        $this->attachmentObjectID = $this->entry->entryID;

        // additional categories
        if (isset($_REQUEST['categoryIDs'])) {
            $this->categoryIDs = ArrayUtil::toIntegerArray($_REQUEST['categoryIDs']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        if (isset($_POST['editReason'])) {
            $this->editReason = StringUtil::trim($_POST['editReason']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (!\count($_POST)) {
            $this->categoryID = $this->entry->categoryID;
            if ($this->entry->languageID) {
                $this->languageID = $this->entry->languageID;
            }
            $this->subject = $this->entry->subject;
            $this->teaser = $this->entry->teaser;
            $this->text = $this->entry->message;

            $this->text2 = $this->entry->text2;
            $this->text3 = $this->entry->text3;
            $this->text4 = $this->entry->text4;
            $this->text5 = $this->entry->text5;

            $this->enableComments = $this->entry->enableComments;

            $this->geocode = $this->entry->location;
            $this->latitude = $this->entry->latitude;
            $this->longitude = $this->entry->longitude;
            if ($this->entry->location) {
                $this->enableCoordinates = 1;
            }

            // icon
            if (SHOW_ENTRY_ICON_ENABLE) {
                $this->iconLocation = $this->entry->getIconURL();
            }

            // labels
            $assignedLabels = EntryLabelObjectHandler::getInstance()->getAssignedLabels([$this->entry->entryID], true);
            if (isset($assignedLabels[$this->entry->entryID])) {
                foreach ($assignedLabels[$this->entry->entryID] as $label) {
                    $this->labelIDs[$label->groupID] = $label->labelID;
                }
            }

            // tags
            if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag')) {
                $tags = TagEngine::getInstance()->getObjectTags('com.uz.show.entry', $this->entry->entryID, [$this->entry->languageID]);
                foreach ($tags as $tag) {
                    $this->tags[] = $tag->name;
                }
            }

            // additional categories
            $this->categoryIDs = [];
            foreach ($this->entry->getCategories() as $category) {
                $this->categoryIDs[] = $category->categoryID;
            }
            if (!empty($this->categoryIDs)) {
                $this->enableCategories = 1;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // validate entry options
        $optionHandlerErrors = $this->optionHandler->validate();
        if (!empty($optionHandlerErrors)) {
            throw new UserInputException('options', $optionHandlerErrors);
        }

        MessageForm::validate();

        // validate category ids
        if (empty($this->categoryID)) {
            throw new UserInputException('categoryID');
        }
        $category = ShowCategory::getCategory($this->categoryID);
        if ($category === null) {
            throw new UserInputException('categoryID', 'invalid');
        }
        if (!$category->isAccessible() || !$category->getPermission('canUseCategory')) {
            throw new UserInputException('categoryID', 'invalid');
        }

        // additional categories
        if (SHOW_CATEGORY_ENABLE) {
            foreach ($this->categoryIDs as $id) {
                $category = ShowCategory::getCategory($id);
                if ($category === null) {
                    throw new UserInputException('categories', 'invalid');
                }
                if (!$category->isAccessible() || !$category->getPermission('canUseCategory')) {
                    throw new UserInputException('categories', 'invalid');
                }
            }

            $cats = \array_unique(\array_merge($this->categoryIDs, [$this->categoryID]));
            if (\count($cats) > WCF::getSession()->getPermission('user.show.maxCategories')) {
                throw new UserInputException('categories', 'tooMany');
            }
        }

        // validate teaser
        if (empty($this->teaser)) {
            throw new UserInputException('teaser');
        }
        if (\mb_strlen($this->teaser) > SHOW_MAX_TEASER_LENGTH) {
            throw new UserInputException('teaser', 'tooLong');
        }
        // search for censored words
        $result = Censorship::getInstance()->test($this->teaser);
        if ($result) {
            WCF::getTPL()->assign('censoredWords', $result);
            throw new UserInputException('teaser', 'censoredWordsFound');
        }

        // geo location data
        if (SHOW_GEODATA_TYPE == 2 && $this->enableCoordinates) {
            if (empty($this->geocode)) {
                $this->enableCoordinates = false;
                $this->latitude = 0.0;
                $this->longitude = 0.0;
            }
        }

        if (SHOW_GEODATA_TYPE == 3) {
            if (empty($this->geocode)) {
                throw new UserInputException('geocode', 'required');
            }
        }

        // texts
        if (SHOW_TAB2_ENABLE && SHOW_TAB2_WYSIWYG) {
            $this->validateText2();
        }
        if (SHOW_TAB3_ENABLE && SHOW_TAB3_WYSIWYG) {
            $this->validateText3();
        }
        if (SHOW_TAB4_ENABLE && SHOW_TAB4_WYSIWYG) {
            $this->validateText4();
        }
        if (SHOW_TAB5_ENABLE && SHOW_TAB5_WYSIWYG) {
            $this->validateText5();
        }

        // labels
        $this->validateLabelIDs();

        // images
        if (SHOW_IMAGES_FORCE) {
            // misconfiguration?
            $tabExists = 0;
            if (SHOW_IMAGES_TAB == 2 && SHOW_TAB2_ENABLE) {
                $tabExists = 1;
            }
            if (SHOW_IMAGES_TAB == 3 && SHOW_TAB3_ENABLE) {
                $tabExists = 1;
            }
            if (SHOW_IMAGES_TAB == 4 && SHOW_TAB4_ENABLE) {
                $tabExists = 1;
            }
            if (SHOW_IMAGES_TAB == 5 && SHOW_TAB5_ENABLE) {
                $tabExists = 1;
            }

            if ($tabExists) {
                if (isset($this->attachmentHandler) && $this->attachmentHandler !== null) {
                    $count = \count($this->attachmentHandler);
                } else {
                    throw new UserInputException('images', 'missing');
                }

                if (!$count) {
                    throw new UserInputException('images', 'missing');
                }
            }
        }
    }

    /**
     * Validates the selected labels.
     */
    protected function validateLabelIDs()
    {
        EntryLabelObjectHandler::getInstance()->setCategoryIDs([$this->categoryID]);

        $validationResult = EntryLabelObjectHandler::getInstance()->validateLabelIDs($this->labelIDs, 'canSetLabel', false);

        // reset category ids to accessible category ids
        EntryLabelObjectHandler::getInstance()->setCategoryIDs(ShowCategory::getAccessibleCategoryIDs());

        if (!empty($validationResult[0])) {
            throw new UserInputException('labelIDs');
        }

        if (!empty($validationResult)) {
            throw new UserInputException('label', $validationResult);
        }
    }

    /**
     * Validates text2.
     */
    protected function validateText2()
    {
        if (empty($this->messageObjectType)) {
            throw new RuntimeException("Expected non-empty message object type for '" . static::class . "'");
        }

        if (empty($this->text2) && SHOW_TAB2_WYSIWYG_FORCE) {
            throw new UserInputException('text2');
        }

        if (!empty($this->text2)) {
            if ($this->disallowedBBCodesPermission) {
                BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(',', WCF::getSession()->getPermission($this->disallowedBBCodesPermission)));
            }

            $this->htmlInputProcessor2 = new HtmlInputProcessor();
            $this->htmlInputProcessor2->process($this->text2, 'com.uz.show.entry.text2', 0);

            // check text length
            if ($this->htmlInputProcessor2->appearsToBeEmpty()) {
                throw new UserInputException('text2');
            }
            $message = $this->htmlInputProcessor2->getTextContent();
            if ($this->maxTextLength != 0 && \mb_strlen($message) > $this->maxTextLength) {
                throw new UserInputException('text2', 'tooLong');
            }

            $disallowedBBCodes = $this->htmlInputProcessor2->validate();
            if (!empty($disallowedBBCodes)) {
                WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
                throw new UserInputException('text2', 'disallowedBBCodes');
            }

            // search for censored words
            $result = Censorship::getInstance()->test($message);
            if ($result) {
                WCF::getTPL()->assign('censoredWords', $result);
                throw new UserInputException('text2', 'censoredWordsFound');
            }
        }
    }

    /**
     * Validates text3.
     */
    protected function validateText3()
    {
        if (empty($this->messageObjectType)) {
            throw new RuntimeException("Expected non-empty message object type for '" . static::class . "'");
        }

        if (empty($this->text3) && SHOW_TAB3_WYSIWYG_FORCE) {
            throw new UserInputException('text3');
        }

        if (!empty($this->text3)) {
            if ($this->disallowedBBCodesPermission) {
                BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(',', WCF::getSession()->getPermission($this->disallowedBBCodesPermission)));
            }

            $this->htmlInputProcessor3 = new HtmlInputProcessor();
            $this->htmlInputProcessor3->process($this->text3, 'com.uz.show.entry.text3', 0);

            // check text length
            if ($this->htmlInputProcessor3->appearsToBeEmpty()) {
                throw new UserInputException('text3');
            }
            $message = $this->htmlInputProcessor3->getTextContent();
            if ($this->maxTextLength != 0 && \mb_strlen($message) > $this->maxTextLength) {
                throw new UserInputException('text3', 'tooLong');
            }

            $disallowedBBCodes = $this->htmlInputProcessor3->validate();
            if (!empty($disallowedBBCodes)) {
                WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
                throw new UserInputException('text3', 'disallowedBBCodes');
            }

            // search for censored words
            $result = Censorship::getInstance()->test($message);
            if ($result) {
                WCF::getTPL()->assign('censoredWords', $result);
                throw new UserInputException('text3', 'censoredWordsFound');
            }
        }
    }

    /**
     * Validates text4.
     */
    protected function validateText4()
    {
        if (empty($this->messageObjectType)) {
            throw new RuntimeException("Expected non-empty message object type for '" . static::class . "'");
        }

        if (empty($this->text4) && SHOW_TAB4_WYSIWYG_FORCE) {
            throw new UserInputException('text4');
        }

        if (!empty($this->text4)) {
            if ($this->disallowedBBCodesPermission) {
                BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(',', WCF::getSession()->getPermission($this->disallowedBBCodesPermission)));
            }

            $this->htmlInputProcessor4 = new HtmlInputProcessor();
            $this->htmlInputProcessor4->process($this->text4, 'com.uz.show.entry.text4', 0);

            // check text length
            if ($this->htmlInputProcessor4->appearsToBeEmpty()) {
                throw new UserInputException('text4');
            }
            $message = $this->htmlInputProcessor4->getTextContent();
            if ($this->maxTextLength != 0 && \mb_strlen($message) > $this->maxTextLength) {
                throw new UserInputException('text4', 'tooLong');
            }

            $disallowedBBCodes = $this->htmlInputProcessor4->validate();
            if (!empty($disallowedBBCodes)) {
                WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
                throw new UserInputException('text4', 'disallowedBBCodes');
            }

            // search for censored words
            $result = Censorship::getInstance()->test($message);
            if ($result) {
                WCF::getTPL()->assign('censoredWords', $result);
                throw new UserInputException('text4', 'censoredWordsFound');
            }
        }
    }

    /**
     * Validates text5.
     */
    protected function validateText5()
    {
        if (empty($this->messageObjectType)) {
            throw new RuntimeException("Expected non-empty message object type for '" . static::class . "'");
        }

        if (empty($this->text5) && SHOW_TAB5_WYSIWYG_FORCE) {
            throw new UserInputException('text5');
        }

        if (!empty($this->text5)) {
            if ($this->disallowedBBCodesPermission) {
                BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(',', WCF::getSession()->getPermission($this->disallowedBBCodesPermission)));
            }

            $this->htmlInputProcessor5 = new HtmlInputProcessor();
            $this->htmlInputProcessor5->process($this->text5, 'com.uz.show.entry.text5', 0);

            // check text length
            if ($this->htmlInputProcessor5->appearsToBeEmpty()) {
                throw new UserInputException('text5');
            }
            $message = $this->htmlInputProcessor5->getTextContent();
            if ($this->maxTextLength != 0 && \mb_strlen($message) > $this->maxTextLength) {
                throw new UserInputException('text5', 'tooLong');
            }

            $disallowedBBCodes = $this->htmlInputProcessor5->validate();
            if (!empty($disallowedBBCodes)) {
                WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
                throw new UserInputException('text5', 'disallowedBBCodes');
            }

            // search for censored words
            $result = Censorship::getInstance()->test($message);
            if ($result) {
                WCF::getTPL()->assign('censoredWords', $result);
                throw new UserInputException('text5', 'censoredWordsFound');
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        MessageForm::save();

        if (SHOW_TAB2_ENABLE && SHOW_TAB2_WYSIWYG && !empty($this->text2)) {
            $this->text2 = $this->htmlInputProcessor2->getHtml();
        }
        if (SHOW_TAB3_ENABLE && SHOW_TAB3_WYSIWYG && !empty($this->text3)) {
            $this->text3 = $this->htmlInputProcessor3->getHtml();
        }
        if (SHOW_TAB4_ENABLE && SHOW_TAB4_WYSIWYG && !empty($this->text4)) {
            $this->text4 = $this->htmlInputProcessor4->getHtml();
        }
        if (SHOW_TAB5_ENABLE && SHOW_TAB5_WYSIWYG && !empty($this->text5)) {
            $this->text5 = $this->htmlInputProcessor5->getHtml();
        }

        // get options
        $saveOptions = $this->optionHandler->save();

        // save labels
        EntryLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $this->entry->entryID);
        $labelIDs = EntryLabelObjectHandler::getInstance()->getAssignedLabels([$this->entry->entryID], false);

        // save entry
        $data = \array_merge($this->additionalFields, [
            'categoryID' => $this->categoryID,
            'hasLabels' => (isset($labelIDs[$this->entry->entryID]) && !empty($labelIDs[$this->entry->entryID])) ? 1 : 0,
            'languageID' => $this->languageID,
            'message' => $this->text,
            'subject' => $this->subject,
            'teaser' => $this->teaser,
            'text2' => $this->text2,
            'text3' => $this->text3,
            'text4' => $this->text4,
            'text5' => $this->text5,
            'location' => $this->geocode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        if (WCF::getSession()->getPermission('user.show.canDisableCommentFunction')) {
            $data['enableComments'] = $this->enableComments;
        }

        $entryData = [
            'isEdit' => true,
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
            'htmlInputProcessor2' => $this->htmlInputProcessor2,
            'htmlInputProcessor3' => $this->htmlInputProcessor3,
            'htmlInputProcessor4' => $this->htmlInputProcessor4,
            'htmlInputProcessor5' => $this->htmlInputProcessor5,
            'options' => $saveOptions,
            'editReason' => $this->editReason,
            'categoryIDs' => $this->categoryIDs,
        ];
        if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag') && WCF::getSession()->getPermission('user.show.canSetTags')) {
            $entryData['tags'] = $this->tags;
        }

        if (SHOW_ENTRY_ICON_ENABLE) {
            $entryData['tmpHash'] = $this->tmpHash;
        }

        $this->objectAction = new EntryAction([$this->entry], 'update', $entryData);
        $this->objectAction->executeAction();
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Entry', [
            'application' => 'show',
            'object' => $this->entry,
        ]));

        exit;
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!$this->entry->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initOptionHandler()
    {
        $this->optionHandler->setEntry($this->entry);
    }

    /**
     * @inheritDoc
     */
    protected function setLocation()
    {
        SHOWCore::getInstance()->setLocation($this->entry->getCategory()->getParentCategories(), $this->entry->getCategory(), $this->entry);
    }
}
