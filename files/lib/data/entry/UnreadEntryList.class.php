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

use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of unread entrys.
 */
class UnreadEntryList extends AccessibleEntryList
{
    /**
     * Creates a new UnreadEntryList object.
     */
    public function __construct()
    {
        parent::__construct();

        $this->sqlConditionJoins .= " LEFT JOIN wcf" . WCF_N . "_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = " . VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry') . " AND tracked_visit.objectID = entry.entryID AND tracked_visit.userID = " . WCF::getUser()->userID . ")";
        if (SHOW_LAST_CHANGE_NEW) {
            $this->getConditionBuilder()->add('entry.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
            $this->getConditionBuilder()->add('(entry.lastChangeTime > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');
        } else {
            $this->getConditionBuilder()->add('entry.time > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
            $this->getConditionBuilder()->add('(entry.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');
        }
    }
}
