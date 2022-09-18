<?php
namespace show\data\entry\option;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of entry options.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = EntryOption::class;
}
