<?php
namespace show\data\contact;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit contacts.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ContactEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Contact::class;
}
