<?php
namespace show\data\contact;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of contacts.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ContactList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Contact::class;
}
