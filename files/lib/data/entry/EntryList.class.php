<?php
namespace show\data\entry;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Entry::class;
}
