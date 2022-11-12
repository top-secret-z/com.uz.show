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
namespace show\system\box;

use show\data\category\ShowCategory;
use wcf\data\comment\ViewableCommentList;
use wcf\system\box\AbstractCommentListBoxController;
use wcf\system\WCF;

/**
 * Box controller implementation for a list of show comments.
 */
class CommentListBoxController extends AbstractCommentListBoxController
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'com.uz.show.entryComment';

    /**
     * @inheritDoc
     */
    protected function applyObjectTypeFilters(ViewableCommentList $commentList)
    {
        $accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
        if (WCF::getSession()->getPermission('user.show.canViewEntry') && !empty($accessibleCategoryIDs)) {
            $commentList->sqlJoins .= ' INNER JOIN show' . WCF_N . '_entry entry ON (comment.objectID = entry.entryID)';
            $commentList->sqlSelects = 'entry.subject AS title';

            $commentList->getConditionBuilder()->add('entry.categoryID IN (?)', [$accessibleCategoryIDs]);

            if (!WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
                $commentList->getConditionBuilder()->add('entry.isDisabled = ?', [0]);
            }
            if (!WCF::getSession()->getPermission('mod.show.canViewDeletedEntry')) {
                $commentList->getConditionBuilder()->add('entry.isDeleted = ?', [0]);
            }
        } else {
            $commentList->getConditionBuilder()->add('0 = 1');
        }
    }
}
