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
namespace show\system\category;

use show\data\entry\EntryAction;
use show\data\entry\EntryList;
use wcf\data\category\CategoryEditor;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type implementation for entry categories.
 */
class ShowCategoryType extends AbstractCategoryType
{
    /**
     * @inheritDoc
     */
    protected $forceDescription = false;

    /**
     * @inheritDoc
     */
    protected $langVarPrefix = 'show.category';

    /**
     * @inheritDoc
     */
    protected $maximumNestingLevel = 3;

    /**
     * @inheritDoc
     */
    protected $objectTypes = ['com.woltlab.wcf.acl' => 'com.uz.show.category'];

    /**
     * @inheritDoc
     */
    public function afterDeletion(CategoryEditor $categoryEditor)
    {
        // delete entrys with no categories
        $entryList = new EntryList();
        $entryList->getConditionBuilder()->add("entry.categoryID IS NULL");
        $entryList->readObjects();

        if (\count($entryList)) {
            $entryAction = new EntryAction($entryList->getObjects(), 'delete');
            $entryAction->executeAction();
        }

        parent::afterDeletion($categoryEditor);
    }

    /**
     * @inheritDoc
     */
    public function canAddCategory()
    {
        return $this->canEditCategory();
    }

    /**
     * @inheritDoc
     */
    public function canDeleteCategory()
    {
        return $this->canEditCategory();
    }

    /**
     * @inheritDoc
     */
    public function canEditCategory()
    {
        return WCF::getSession()->getPermission('admin.show.canManageCategory');
    }

    /**
     * @inheritDoc
     */
    public function supportsHtmlDescription()
    {
        return true;
    }
}
