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
namespace show\page;

use show\data\category\ShowCategory;
use show\data\entry\CategoryEntryList;
use show\system\SHOWCore;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of entrys in a certain category.
 */
class CategoryEntryListPage extends EntryListPage
{
    /**
     * category the listed entrys belong to
     */
    public $category;

    public $categoryID = 0;

    /**
     * @inheritDoc
     */
    public $controllerName = 'CategoryEntryList';

    /**
     * @inheritDoc
     */
    public $templateName = 'entryList';

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'categoryID' => $this->categoryID,
            'category' => $this->category,
            'controllerObject' => $this->category,
            'feedControllerName' => 'CategoryEntryListFeed',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        SHOWCore::getInstance()->setLocation($this->category->getParentCategories());
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
        }
        $this->category = ShowCategory::getCategory($this->categoryID);
        if ($this->category === null) {
            throw new IllegalLinkException();
        }
        $this->controllerParameters['object'] = $this->category;
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryEntryList', [
            'application' => 'show',
            'object' => $this->category,
        ], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));

        $this->labelGroups = $this->category->getLabelGroups('canViewLabel');
    }

    /**
     * @inheritDoc
     */
    public function checkPermissions()
    {
        parent::checkPermissions();

        if (!$this->category->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        $this->objectList = new CategoryEntryList($this->categoryID, true);

        $this->applyFilters();
    }
}
