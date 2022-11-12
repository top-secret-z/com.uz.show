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
namespace show\data\entry;

use show\data\category\ShowCategory;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of accessible entrys.
 */
class AccessibleEntryList extends ViewableEntryList
{
    protected $applyCategoryFilter = true;

    /**
     * Creates a new AccessibleEntryList object.
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->applyCategoryFilter) {
            $accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
            if (!empty($accessibleCategoryIDs)) {
                $this->getConditionBuilder()->add('entry.categoryID IN (?)', [$accessibleCategoryIDs]);
            } else {
                $this->getConditionBuilder()->add('1=0');
            }
        }

        if (!WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
            if (!WCF::getUser()->userID) {
                $this->getConditionBuilder()->add('entry.isDisabled = 0');
            } else {
                $this->getConditionBuilder()->add('(entry.isDisabled = 0 OR entry.userID = ?)', [WCF::getUser()->userID]);
            }
        }

        if (!WCF::getSession()->getPermission('mod.show.canViewDeletedEntry')) {
            $this->getConditionBuilder()->add('entry.isDeleted = 0');
        }

        // apply language filter
        if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $this->getConditionBuilder()->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
        }
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        if ($this->objectIDs === null) {
            $this->readObjectIDs();
        }

        parent::readObjects();
    }
}
