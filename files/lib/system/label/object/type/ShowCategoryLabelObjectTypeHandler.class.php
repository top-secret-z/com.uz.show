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
namespace show\system\label\object\type;

use show\data\category\ShowCategoryNodeTree;
use show\system\cache\builder\ShowCategoryLabelCacheBuilder;
use wcf\system\label\object\type\AbstractLabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectType;
use wcf\system\label\object\type\LabelObjectTypeContainer;

/**
 * Object type handler for show categories.
 */
class ShowCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler
{
    /**
     * category list
     */
    public $categoryList;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $categoryTree = new ShowCategoryNodeTree('com.uz.show.category');
        $this->categoryList = $categoryTree->getIterator();
        $this->categoryList->setMaxDepth(0);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        ShowCategoryLabelCacheBuilder::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function setObjectTypeID($objectTypeID)
    {
        parent::setObjectTypeID($objectTypeID);

        $this->container = new LabelObjectTypeContainer($this->objectTypeID);

        foreach ($this->categoryList as $category) {
            $this->container->add(new LabelObjectType($category->getTitle(), $category->categoryID, 0));
            foreach ($category as $subCategory) {
                $this->container->add(new LabelObjectType($subCategory->getTitle(), $subCategory->categoryID, 1));
                foreach ($subCategory as $subSubCategory) {
                    $this->container->add(new LabelObjectType($subSubCategory->getTitle(), $subSubCategory->categoryID, 2));
                }
            }
        }
    }
}
