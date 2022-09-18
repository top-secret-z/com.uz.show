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
namespace show\system\label\object;

use show\system\cache\builder\ShowCategoryLabelCacheBuilder;
use wcf\system\label\LabelHandler;
use wcf\system\label\object\AbstractLabelObjectHandler;

/**
 * Label handler for entrys.
 */
class EntryLabelObjectHandler extends AbstractLabelObjectHandler
{
    /**
     * @inheritDoc
     */
    protected $objectType = 'com.uz.show.entry';

    /**
     * Sets the label groups available for the categories with the given ids.
     */
    public function setCategoryIDs($categoryIDs)
    {
        $labelGroupsToCategories = ShowCategoryLabelCacheBuilder::getInstance()->getData();

        $groupIDs = [];
        foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
            if (\in_array($categoryID, $categoryIDs)) {
                $groupIDs = \array_merge($groupIDs, $__groupIDs);
            }
        }

        $this->labelGroups = [];
        if (!empty($groupIDs)) {
            $this->labelGroups = LabelHandler::getInstance()->getLabelGroups(\array_unique($groupIDs));
        }
    }
}
