<?php
namespace show\page;
use show\data\entry\WatchedEntryList;
use wcf\system\WCF;

/**
 * Shows the list of watched entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class WatchedEntryListPage extends EntryListPage {
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'WatchedEntryList';
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = WatchedEntryList::class;
	
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
