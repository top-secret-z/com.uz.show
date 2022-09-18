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
namespace show\data\category;

use wcf\data\category\CategoryNode;

/**
 * Represents a show category node.
 */
class ShowCategoryNode extends CategoryNode
{
    /**
     * number of entrys / unread entrys of the category
     */
    protected $entrys;

    protected $unreadEntrys;

    /**
     * @inheritDoc
     */
    protected static $baseClass = ShowCategory::class;

    /**
     * Returns entry count of the category.
     */
    public function getEntrys()
    {
        if ($this->entrys === null) {
            $this->entrys = ShowCategoryCache::getInstance()->getEntrys($this->categoryID);
        }

        return $this->entrys;
    }

    /**
     * Returns unread entry count of the category.
     */
    public function getUnreadEntrys()
    {
        if ($this->unreadEntrys === null) {
            $this->unreadEntrys = ShowCategoryCache::getInstance()->getUnreadEntrys($this->categoryID);
        }

        return $this->unreadEntrys;
    }
}
