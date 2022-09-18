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

use InvalidArgumentException;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds support for sorting by entry count to the user list box controller.
 */
class UserListBoxControllerListener implements IParameterizedEventListener
{
    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        switch ($eventName) {
            case '__construct':
                $eventObj->validSortFields[] = 'showEntrys';
                break;

            case 'readObjects':
                // only consider users with at least one entry
                if ($eventObj->sortField === 'showEntrys') {
                    $eventObj->objectList->getConditionBuilder()->add('user_table.showEntrys > 0');
                }
                break;

            default:
                throw new InvalidArgumentException("Cannot handle event '{$eventName}'");
        }
    }
}
