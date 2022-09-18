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
namespace show\acp\page;

use show\data\entry\option\EntryOptionList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the list of entry options.
 */
class EntryOptionListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'show.acp.menu.link.show.entry.option.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.show.canManageEntryOption'];

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'showOrder';

    /**
     * @inheritDoc
     */
    public $objectListClassName = EntryOptionList::class;

    /**
     * @inheritDoc
     */
    public $validSortFields = ['optionID', 'optionTitle', 'optionType', 'showOrder', 'tab'];

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        $titles[1] = WCF::getLanguage()->get(SHOW_TAB1_TITLE);
        $titles[2] = WCF::getLanguage()->get(SHOW_TAB2_TITLE);
        $titles[3] = WCF::getLanguage()->get(SHOW_TAB3_TITLE);
        $titles[4] = WCF::getLanguage()->get(SHOW_TAB4_TITLE);
        $titles[5] = WCF::getLanguage()->get(SHOW_TAB5_TITLE);

        WCF::getTPL()->assign([
            'titles' => $titles,
        ]);
    }
}
