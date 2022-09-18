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
use show\data\category\ShowCategoryNodeTree;
use show\system\SHOWCore;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows the map with all entry locations.
 */
class MapPage extends AbstractPage
{
    /**
     * category
     */
    public $categoryList;

    public $categoryID = 0;

    public $category;

    /**
     * @inheritDoc
     */
    public $neededModules = ['GOOGLE_MAPS_API_KEY', 'SHOW_GEODATA_MAP_ENABLE'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.show.canViewEntry'];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // categorized list
        if (isset($_REQUEST['id'])) {
            $this->categoryID = \intval($_REQUEST['id']);
            $this->category = ShowCategory::getCategory($this->categoryID);
            if ($this->category === null) {
                throw new IllegalLinkException();
            }
            if (!$this->category->isAccessible()) {
                throw new PermissionDeniedException();
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
        $categoryTree = new ShowCategoryNodeTree('com.uz.show.category');
        $this->categoryList = $categoryTree->getIterator();
        $this->categoryList->setMaxDepth(0);

        SHOWCore::getInstance()->setLocation();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'category' => $this->category,
            'categoryID' => $this->categoryID,
            'categoryList' => $this->categoryList,
        ]);
    }
}
