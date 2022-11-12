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
namespace show\system\page\handler;

use show\data\category\ShowCategory;
use show\data\category\ShowCategoryCache;
use wcf\system\page\handler\AbstractLookupPageHandler;
use wcf\system\page\handler\IOnlineLocationPageHandler;
use wcf\system\page\handler\TDecoratedCategoryOnlineLocationLookupPageHandler;

/**
 * Menu page handler for the category entry list page.
 */
class CategoryEntryListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TDecoratedCategoryOnlineLocationLookupPageHandler;

    /**
     * @inheritDoc
     */
    protected function getDecoratedCategoryClass()
    {
        return ShowCategory::class;
    }

    /**
     * @inheritDoc
     */
    public function getOutstandingItemCount($objectID = null)
    {
        return ShowCategoryCache::getInstance()->getUnreadEntrys($objectID);
    }
}
