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
namespace show\system;

use show\data\category\ShowCategory;
use show\data\entry\Entry;
use show\page\EntryListPage;
use wcf\system\application\AbstractApplication;
use wcf\system\page\PageLocationManager;

/**
 * This class extends the main WCF class by show specific functions.
 */
class SHOWCore extends AbstractApplication
{
    /**
     * @inheritDoc
     */
    protected $primaryController = EntryListPage::class;

    /**
     * Sets location data.
     */
    public function setLocation(array $parentCategories = [], ?ShowCategory $category = null, ?Entry $entry = null)
    {
        // add entry
        if ($entry !== null) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.show.Entry', $entry->entryID, $entry);
        }

        // add category
        if ($category !== null) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.show.CategoryEntryList', $category->categoryID, $category, true);
        }

        // add parent categories
        $parentCategories = \array_reverse($parentCategories);
        foreach ($parentCategories as $parentCategory) {
            PageLocationManager::getInstance()->addParentLocation('com.uz.show.CategoryEntryList', $parentCategory->categoryID, $parentCategory);
        }
    }
}
