<?php
namespace show\data\contact;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes contact-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ContactAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ContactEditor::class;
}
