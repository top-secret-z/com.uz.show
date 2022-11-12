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
namespace show\data\contact;

use wcf\data\DatabaseObject;
use wcf\system\WCF;

/**
 * Represents an contact.
 */
class Contact extends DatabaseObject
{
    /**
     * Returns true if the active user can edit this contact.
     */
    public function canEdit()
    {
        if ($this->userID == WCF::getUser()->userID) {
            return true;
        }

        return false;
    }

    /**
     * Get user's contact data
     */
    public static function getContactData($userID, $isDisabled = true)
    {
        if ($isDisabled) {
            $sql = "SELECT    *
                    FROM    show" . WCF_N . "_contact
                    WHERE    userID = ? AND isDisabled = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$userID, 0]);
        } else {
            $sql = "SELECT    *
                    FROM    show" . WCF_N . "_contact
                    WHERE    userID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$userID]);
        }

        $row = $statement->fetchArray();
        if (!$row) {
            $row = [];
        }

        return new self(null, $row);
    }

    /**
     * check contact
     */
    public static function checkContact($userID)
    {
        if (!SHOW_CONTACT_ENABLE || !WCF::getSession()->getPermission('user.show.canViewContact')) {
            return false;
        }

        $data = self::getContactData($userID);
        if (!$data->contactID) {
            return false;
        }
        if (empty($data->name) && empty($data->address) && empty($data->email) && empty($data->website) && empty($data->other)) {
            return false;
        }

        return true;
    }
}
