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
namespace show\system\event\listener;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Updates the user activity point items counter for Show entries.
 */
class UserActivityPointItemsRebuildDataWorkerListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $objectType = UserActivityPointHandler::getInstance()->getObjectTypeByName('com.uz.show.activityPointEvent.entry');

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('user_activity_point.objectTypeID = ?', [$objectType->objectTypeID]);
        $conditionBuilder->add('user_activity_point.userID IN (?)', [$eventObj->getObjectList()->getObjectIDs()]);

        $sql = "UPDATE        wcf" . WCF_N . "_user_activity_point user_activity_point
                LEFT JOIN    wcf" . WCF_N . "_user user_table
                ON        (user_table.userID = user_activity_point.userID)
                SET        user_activity_point.items = user_table.showEntrys,
                        user_activity_point.activityPoints = user_activity_point.items * ?
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute(\array_merge(
            [$objectType->points],
            $conditionBuilder->getParameters()
        ));
    }
}
