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
namespace show\system\option;

use show\data\entry\Entry;
use show\system\cache\builder\EntryOptionCacheBuilder;
use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\option\OptionHandler;

/**
 * Handles entry options.
 */
class EntryOptionHandler extends OptionHandler
{
    /**
     * true if within edit mode
     */
    public $editMode = true;

    /**
     * current entry
     */
    public $entry;

    /**
     * Enables edit mode.
     */
    public function enableEditMode($enable = true)
    {
        $this->editMode = $enable;
    }

    /**
     * @inheritDoc
     */
    public function getOption($optionName)
    {
        $optionData = parent::getOption($optionName);

        if (!$this->editMode && isset($this->optionValues[$optionName])) {
            $optionData['object']->setOptionValue($this->optionValues[$optionName]);
        }

        return $optionData;
    }

    /**
     * Returns the parsed options.
     */
    public function getOptions()
    {
        $parsedOptions = [];
        foreach ($this->options as $option) {
            $parsedOptions[] = $this->getOption($option->optionName);
        }

        return $parsedOptions;
    }

    /**
     * Returns the option values.
     */
    public function getOptionValues()
    {
        return $this->optionValues;
    }

    /**
     * Initializes active options.
     */
    public function init()
    {
        if (!$this->didInit) {
            // get active options
            foreach ($this->cachedOptions as $option) {
                if ($this->checkOption($option)) {
                    $this->options[$option->optionName] = $option;
                }
            }

            // mark options as initialized
            $this->didInit = true;
        }
    }

    /**
     * Gets all options and option categories from cache.
     */
    protected function readCache()
    {
        $this->cachedOptions = EntryOptionCacheBuilder::getInstance()->getData();
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        foreach ($this->options as $option) {
            if (!isset($this->optionValues[$option->optionName])) {
                $this->optionValues[$option->optionName] = $option->defaultValue;
            }
        }
    }

    /**
     * Resets the option values.
     */
    public function resetOptionValues()
    {
        $this->optionValues = [];
    }

    /**
     * Sets option values for a certain entry.
     */
    public function setEntry(Entry $entry)
    {
        $this->optionValues = [];
        $this->entry = $entry;

        $this->init();
        foreach ($this->options as $option) {
            $this->optionValues[$option->optionName] = $this->entry->getOptionValue($option->optionID);
        }
    }

    /**
     * Sets the option values.
     */
    public function setOptionValues(array $values)
    {
        $this->optionValues = $values;
    }

    /**
     * @inheritDoc
     */
    protected function validateOption(Option $option)
    {
        parent::validateOption($option);

        if ($option->required && $option->optionType != 'boolean' && empty($this->optionValues[$option->optionName])) {
            throw new UserInputException($option->optionName);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getFormElement($type, Option $option)
    {
        // remove required from options; fix for Chrome and to get understandable error indications
        $html = $this->getTypeObject($type)->getFormElement($option, ($this->optionValues[$option->optionName] ?? null));

        return \str_replace(' required', '', $html);
    }
}
