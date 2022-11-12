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
namespace show\system\upload;

use show\data\entry\Entry;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;

use const PHP_INT_MAX;

/**
 * Validates uploaded entry icons.
 */
class EntryIconUploadEntryValidationStrategy extends DefaultUploadFileValidationStrategy
{
    /**
     * Creates a new EntryIconUploadEntryValidationStrategy object.
     */
    public function __construct()
    {
        parent::__construct(PHP_INT_MAX, ['jpg', 'jpeg', 'png']);
    }

    /**
     * @inheritDoc
     */
    public function validate(UploadFile $uploadFile)
    {
        if (parent::validate($uploadFile)) {
            // check if entry is an image
            $imageData = $uploadFile->getImageData();
            if ($imageData === null) {
                $uploadFile->setValidationErrorType('noImage');

                return false;
            }

            // check if image is too small
            if ($imageData['height'] < Entry::ICON_SIZE || $imageData['width'] < Entry::ICON_SIZE) {
                $uploadFile->setValidationErrorType('tooSmall');

                return false;
            }
        }

        return true;
    }
}
