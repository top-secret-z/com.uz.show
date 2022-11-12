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

use show\data\entry\FeedEntryList;
use wcf\page\AbstractFeedPage;
use wcf\system\WCF;

/**
 * Shows entrys in feed.
 */
class EntryListFeedPage extends AbstractFeedPage
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['user.show.canViewEntry'];

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'supportsEnclosure' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // read the entrys
        $this->items = new FeedEntryList();
        $this->items->sqlLimit = 20;
        $this->items->readObjects();
        $this->title = WCF::getLanguage()->get('show.entry.entrys');
    }
}
