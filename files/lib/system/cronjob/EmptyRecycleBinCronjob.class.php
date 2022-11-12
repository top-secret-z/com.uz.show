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
namespace show\system\cronjob;

use PDO;
use show\data\entry\EntryAction;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Deletes thrashed entries.
 */
class EmptyRecycleBinCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        if (SHOW_ENTRY_EMPTY_RECYCLE_BIN_CYCLE) {
            $sql = "SELECT    entryID
                    FROM    show" . WCF_N . "_entry
                    WHERE    isDeleted = ? AND deleteTime < ?";
            $statement = WCF::getDB()->prepareStatement($sql, 1000);
            $statement->execute([1, TIME_NOW - SHOW_ENTRY_EMPTY_RECYCLE_BIN_CYCLE * 86400]);
            $entryIDs = $statement->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($entryIDs)) {
                $action = new EntryAction($entryIDs, 'delete');
                $action->executeAction();
            }
        }
    }
}
