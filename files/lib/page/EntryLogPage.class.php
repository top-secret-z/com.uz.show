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

use show\data\entry\ViewableEntry;
use show\data\modification\log\EntryLogModificationLogList;
use show\system\SHOWCore;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the entry log page.
 */
class EntryLogPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $defaultSortField = 'time';

    public $defaultSortOrder = 'DESC';

    public $validSortFields = ['logID', 'time', 'username'];

    /**
     * entry data
     */
    public $entryID = 0;

    public $entry;

    /**
     * @inheritDoc
     */
    public $objectListClassName = EntryLogModificationLogList::class;

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['mod.show.canEditEntry'];

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'entry' => $this->entry,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // add breadcrumbs
        if (!SHOW_CATEGORY_ENABLE) {
            SHOWCore::getInstance()->setLocation($this->entry->getCategory()->getParentCategories(), $this->entry->getCategory(), $this->entry->getDecoratedObject());
        } else {
            SHOWCore::getInstance()->setLocation([], null, $this->entry->getDecoratedObject());
        }
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->entryID = \intval($_REQUEST['id']);
        }
        $this->entry = ViewableEntry::getEntry($this->entryID);
        if ($this->entry === null) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->setEntry($this->entry->getDecoratedObject());
    }
}
