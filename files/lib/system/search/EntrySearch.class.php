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
namespace show\system\search;

use show\data\category\ShowCategory;
use show\data\category\ShowCategoryNodeTree;
use show\data\entry\SearchResultEntryList;
use wcf\data\search\ISearchResultObject;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\search\AbstractSearchProvider;
use wcf\system\WCF;

/**
 * An implementation of ISearchableObjectType for searching in entrys.
 */
final class EntrySearch extends AbstractSearchProvider
{
    /**
     * data
     */
    private $showCategoryID = 0;

    private $messageCache = [];

    /**
     * @inheritDoc
     */
    public function cacheObjects(array $objectIDs, ?array $additionalData = null): void
    {
        $entryList = new SearchResultEntryList();
        $entryList->setObjectIDs($objectIDs);
        $entryList->readObjects();
        foreach ($entryList->getObjects() as $entry) {
            $this->messageCache[$entry->entryID] = $entry;
        }
    }

    /**
     * @inheritDoc
     */
    public function getObject(int $objectID): ?ISearchResultObject
    {
        return $this->messageCache[$objectID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getTableName(): string
    {
        return 'show' . WCF_N . '_entry';
    }

    /**
     * @inheritDoc
     */
    public function getIDFieldName(): string
    {
        return $this->getTableName() . '.entryID';
    }

    /**
     * @inheritDoc
     */
    public function getConditionBuilder(array $parameters): ?PreparedStatementConditionBuilder
    {
        $this->readParameters($parameters);

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $this->initCategoryCondition($conditionBuilder);
        $this->initMiscConditions($conditionBuilder);
        $this->initLanguageCondition($conditionBuilder);

        return $conditionBuilder;
    }

    private function initCategoryCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $selectedCategoryIDs = $this->getShowCategoryIDs($this->showCategoryID);
        $accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
        if (!empty($selectedCategoryIDs)) {
            $selectedCategoryIDs = \array_intersect($selectedCategoryIDs, $accessibleCategoryIDs);
        } else {
            $selectedCategoryIDs = $accessibleCategoryIDs;
        }

        if (empty($selectedCategoryIDs)) {
            $conditionBuilder->add('1=0');
        } else {
            $conditionBuilder->add($this->getTableName() . '.categoryID IN (?)', [$selectedCategoryIDs]);
        }
    }

    private function getShowCategoryIDs(int $categoryID): array
    {
        $categoryIDs = [];

        if ($categoryID) {
            if (($category = ShowCategory::getCategory($categoryID)) !== null) {
                $categoryIDs[] = $categoryID;
                foreach ($category->getAllChildCategories() as $childCategory) {
                    $categoryIDs[] = $childCategory->categoryID;
                }
            }
        }

        return $categoryIDs;
    }

    private function initMiscConditions(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        $conditionBuilder->add($this->getTableName() . '.isDisabled = 0');
        $conditionBuilder->add($this->getTableName() . '.isDeleted = 0');
    }

    private function initLanguageCondition(PreparedStatementConditionBuilder $conditionBuilder): void
    {
        if (LanguageFactory::getInstance()->multilingualismEnabled() && \count(WCF::getUser()->getLanguageIDs())) {
            $conditionBuilder->add(
                '(' . $this->getTableName() . '.languageID IN (?) OR ' . $this->getTableName() . '.languageID IS NULL)',
                [WCF::getUser()->getLanguageIDs()]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getFormTemplateName(): string
    {
        return 'searchEntry';
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalData(): ?array
    {
        return ['showCategoryID' => $this->showCategoryID];
    }

    /**
     * @inheritDoc
     */
    public function assignVariables(): void
    {
        WCF::getTPL()->assign([
            'showCategoryList' => (new ShowCategoryNodeTree('com.uz.show.category'))->getIterator(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('user.show.canViewEntry');
    }

    private function readParameters(array $parameters): void
    {
        if (!empty($parameters['showCategoryID'])) {
            $this->showCategoryID = \intval($parameters['showCategoryID']);
        }
    }
}
