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
use show\data\entry\CategoryFeedEntryList;
use wcf\page\AbstractFeedPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Shows entrys for the specified categories in feed.
 */
class CategoryEntryListFeedPage extends EntryListFeedPage
{
    /**
     * category the listed entrys belong to
     */
    public $category;

    public $categoryID = 0;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        AbstractFeedPage::readData();

        // read the entrys
        $this->items = new CategoryFeedEntryList($this->categoryID, true);
        $this->items->sqlLimit = 20;
        $this->items->readObjects();
        $this->title = $this->category->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
        }
        $this->category = ShowCategory::getCategory($this->categoryID);
        if ($this->category === null) {
            throw new IllegalLinkException();
        }
        if (!$this->category->isAccessible()) {
            throw new PermissionDeniedException();
        }
    }
}
