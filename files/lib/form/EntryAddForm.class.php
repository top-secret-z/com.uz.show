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
use show\data\category\ShowCategoryNodeTree;
use show\data\entry\Entry;
use show\data\entry\EntryAction;
use show\system\cache\builder\ShowCategoryLabelCacheBuilder;
use show\system\label\object\EntryLabelObjectHandler;
use show\system\option\EntryOptionHandler;
use show\system\SHOWCore;
use wcf\form\MessageForm;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\censorship\Censorship;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\HeaderUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Shows the new entry form.
 */
class EntryAddForm extends MessageForm
{
    /**
     * @inheritDoc
     */
    public $attachmentObjectType = 'com.uz.show.entry';

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.show.canAddEntry'];

    /**
     * category related
     */
    public $categoryID = 0;

    public $categoryList;

    /**
     * enables the comment function
     */
    public $enableComments = 1;

    /**
     * @inheritDoc
     */
    public $enableMultilingualism = SHOW_ENABLE_MULTILINGUALISM;

    /**
     * tags
     */
    public $tags = [];

    /**
     * option handler object
     */
    public $optionHandler;

    /**
     * user id of the entry owner
     */
    public $entryOwnerID = 0;

    /**
     * icon data
     */
    public $tmpHash = '';

    public $iconLocation = '';

    /**
     * label data
     */
    public $labelGroups;

    public $labelIDs = [];

    public $labelGroupsToCategories = [];

    /**
     * @inheritDoc
     */
    public $messageObjectType = 'com.uz.show.entry';

    /**
     * teaser text
     */
    public $teaser = '';

    /**
     * tab texts
     */
    public $htmlInputProcessor2;

    public $htmlInputProcessor3;

    public $htmlInputProcessor4;

    public $htmlInputProcessor5;

    public $text2 = '';

    public $text3 = '';

    public $text4 = '';

    public $text5 = '';

    /**
     * geo location data
     */
    public $geocode = '';

    public $latitude = 0.0;

    public $longitude = 0.0;

    public $enableCoordinates = 0;

    /**
     * additional category data
     */
    public $enableCategories = 0;

    public $categoryIDs = [];

    public $flexCategoryList;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        $this->entryOwnerID = WCF::getUser()->userID;

        if (!empty($_REQUEST['categoryID'])) {
            $this->categoryID = \intval($_REQUEST['categoryID']);
        }
        if (isset($_REQUEST['categoryIDs'])) {
            $this->categoryIDs = ArrayUtil::toIntegerArray($_REQUEST['categoryIDs']);
        }

        // get max text length
        $this->maxTextLength = WCF::getSession()->getPermission('user.show.maxTextLength');

        // init options
        $this->optionHandler = new EntryOptionHandler(false);
        $this->initOptionHandler();

        if (isset($_REQUEST['tmpHash'])) {
            $this->tmpHash = StringUtil::trim($_REQUEST['tmpHash']);
        }
        if (empty($this->tmpHash)) {
            $this->tmpHash = StringUtil::getRandomID();
        }

        // labels
        EntryLabelObjectHandler::getInstance()->setCategoryIDs(ShowCategory::getAccessibleCategoryIDs());
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        $this->optionHandler->readUserInput($_POST);

        if (isset($_POST['labelIDs']) && \is_array($_POST['labelIDs'])) {
            $this->labelIDs = $_POST['labelIDs'];
        }
        if (isset($_POST['tags']) && \is_array($_POST['tags'])) {
            $this->tags = ArrayUtil::trim($_POST['tags']);
        }
        if (WCF::getSession()->getPermission('user.show.canDisableCommentFunction')) {
            if (isset($_POST['enableComments'])) {
                $this->enableComments = 1;
            } else {
                $this->enableComments = 0;
            }
        }
        if (isset($_POST['teaser'])) {
            $this->teaser = StringUtil::trim($_POST['teaser']);
        }

        if (SHOW_CATEGORY_ENABLE) {
            if (!empty($_POST['enableCategories'])) {
                $this->enableCategories = 1;
            }
        }

        // geo location
        $this->enableCoordinates = 0;
        if (SHOW_GEODATA_TYPE == 2 && isset($_POST['enableCoordinates'])) {
            $this->enableCoordinates = 1;
        }
        if (SHOW_GEODATA_TYPE == 3) {
            $this->enableCoordinates = 1;
        }

        if ($this->enableCoordinates) {
            if (isset($_POST['geocode'])) {
                $this->geocode = StringUtil::trim($_POST['geocode']);
            }
            if (isset($_POST['latitude'])) {
                $this->latitude = \floatval($_POST['latitude']);
            }
            if (isset($_POST['longitude'])) {
                $this->longitude = \floatval($_POST['longitude']);
            }
        }

        if (SHOW_TAB2_ENABLE && SHOW_TAB2_WYSIWYG && isset($_POST['text2'])) {
            $this->text2 = StringUtil::trim(MessageUtil::stripCrap($_POST['text2']));
        }
        if (SHOW_TAB3_ENABLE && SHOW_TAB3_WYSIWYG && isset($_POST['text3'])) {
            $this->text3 = StringUtil::trim(MessageUtil::stripCrap($_POST['text3']));
        }
        if (SHOW_TAB4_ENABLE && SHOW_TAB4_WYSIWYG && isset($_POST['text4'])) {
            $this->text4 = StringUtil::trim(MessageUtil::stripCrap($_POST['text4']));
        }
        if (SHOW_TAB5_ENABLE && SHOW_TAB5_WYSIWYG && isset($_POST['text5'])) {
            $this->text5 = StringUtil::trim(MessageUtil::stripCrap($_POST['text5']));
        }

        if (SHOW_ENTRY_ICON_ENABLE) {
            $iconExtension = WCF::getSession()->getVar('showEntryIcon-' . $this->tmpHash);
            if ($iconExtension && \file_exists(SHOW_DIR . 'images/entry/' . $this->tmpHash . '.' . $iconExtension)) {
                $this->iconLocation = WCF::getPath('show') . 'images/entry/' . $this->tmpHash . '.' . $iconExtension;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // get categories
        $excludedCategoryIDs = \array_diff(ShowCategory::getAccessibleCategoryIDs(), ShowCategory::getAccessibleCategoryIDs(['canUseCategory']));
        $categoryTree = new ShowCategoryNodeTree('com.uz.show.category', 0, false, $excludedCategoryIDs);
        $this->categoryList = $categoryTree->getIterator();

        $this->flexCategoryList = $categoryTree->getIterator();
        $this->flexCategoryList->setMaxDepth(0);

        // selected categories and parent categories
        foreach ($this->categoryIDs as $categoryID) {
            $category = ShowCategory::getCategory($categoryID);
            if ($category) {
                $this->categoryIDs[] = $category->categoryID;

                if ($category->parentCategoryID) {
                    $this->categoryIDs[] = $category->parentCategoryID;
                }
            }
        }
        $this->categoryIDs = \array_unique($this->categoryIDs);

        $this->labelGroupsToCategories = ShowCategoryLabelCacheBuilder::getInstance()->getData();
        $this->labelGroups = ShowCategory::getAccessibleLabelGroups();

        if (empty($_POST)) {
            // multilingualism
            if (!empty($this->availableContentLanguages)) {
                if (!$this->languageID) {
                    $language = LanguageFactory::getInstance()->getUserLanguage();
                    $this->languageID = $language->languageID;
                }

                if (!isset($this->availableContentLanguages[$this->languageID])) {
                    $languageIDs = \array_keys($this->availableContentLanguages);
                    $this->languageID = \array_shift($languageIDs);
                }
            }

            // set default entry option values
            $this->optionHandler->readData();
        }

        // set location
        $this->setLocation();
    }

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
            'action' => 'add',
            'categoryNodeList' => $this->categoryList,
            'categoryID' => $this->categoryID,
            'enableComments' => $this->enableComments,
            'iconLocation' => $this->iconLocation,
            'labelGroups' => $this->labelGroups,
            'labelGroupsToCategories' => $this->labelGroupsToCategories,
            'labelIDs' => $this->labelIDs,
            'options' => $this->optionHandler->getOptions(),
            'tabs' => $tabs,
            'tab1Options' => $tab1Options,
            'tags' => $this->tags,
            'teaser' => $this->teaser,
            'text2' => $this->text2,
            'text3' => $this->text3,
            'text4' => $this->text4,
            'text5' => $this->text5,
            'tmpHash' => $this->tmpHash,
            'enableCoordinates' => $this->enableCoordinates,
            'enableCategories' => $this->enableCategories,
            'flexCategoryList' => $this->flexCategoryList,
            'categoryIDs' => $this->categoryIDs,
            'geocode' => $this->geocode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        // validate entry options
        $optionHandlerErrors = $this->optionHandler->validate();

        parent::validate();

        if (!empty($optionHandlerErrors)) {
            throw new UserInputException('options', $optionHandlerErrors);
        }

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
        parent::save();

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

        // save entry
        $data = \array_merge($this->additionalFields, [
            'languageID' => $this->languageID,
            'subject' => $this->subject,
            'time' => TIME_NOW,
            'userID' => WCF::getUser()->userID,
            'username' => WCF::getUser()->username,
            'teaser' => $this->teaser,
            'enableComments' => $this->enableComments,
            'isDisabled' => WCF::getSession()->getPermission('user.show.canAddEntryWithoutModeration') ? 0 : 1,
            'hasLabels' => empty($this->labelIDs) ? 0 : 1,
            'categoryID' => $this->categoryID,
            'text2' => $this->text2,
            'text3' => $this->text3,
            'text4' => $this->text4,
            'text5' => $this->text5,
            'location' => $this->geocode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        $entryData = [
            'data' => $data,
            'attachmentHandler' => $this->attachmentHandler,
            'htmlInputProcessor' => $this->htmlInputProcessor,
            'htmlInputProcessor2' => $this->htmlInputProcessor2,
            'htmlInputProcessor3' => $this->htmlInputProcessor3,
            'htmlInputProcessor4' => $this->htmlInputProcessor4,
            'htmlInputProcessor5' => $this->htmlInputProcessor5,
            'options' => $saveOptions,
            'categoryIDs' => $this->categoryIDs,
        ];
        if (MODULE_TAGGING && WCF::getSession()->getPermission('user.tag.canViewTag') && WCF::getSession()->getPermission('user.show.canSetTags')) {
            $entryData['tags'] = $this->tags;
        }
        if (SHOW_ENTRY_ICON_ENABLE) {
            $entryData['tmpHash'] = $this->tmpHash;
        }

        $this->objectAction = new EntryAction([], 'create', $entryData);
        $entry = $this->objectAction->executeAction()['returnValues'];

        // save labels
        if (!empty($this->labelIDs)) {
            EntryLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $entry->entryID);
        }

        // call saved event
        $this->saved();

        HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Entry', [
            'application' => 'show',
            'object' => $entry,
        ]));

        exit;
    }

    /**
     * Initializes the option handler.
     */
    protected function initOptionHandler()
    {
        $this->optionHandler->init();
    }

    /**
     * Sets location.
     */
    protected function setLocation()
    {
        SHOWCore::getInstance()->setLocation();
    }
}
