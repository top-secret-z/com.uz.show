<?php
namespace show\page;
use show\data\entry\UnreadEntryList;
use wcf\system\WCF;

/**
 * Shows the list of unread entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UnreadEntryListPage extends EntryListPage {
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'UnreadEntryList';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UnreadEntryList::class;
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'entryList';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'feedControllerName' => ''
		]);
	}
}
