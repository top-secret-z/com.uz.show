<?php
namespace show\system\page\handler;
use show\data\entry\ViewableEntry;
use wcf\system\page\handler\AbstractMenuPageHandler;

/**
 * Provides the number of unread entrys for menu display.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryListPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return ViewableEntry::getUnreadEntrys();
	}
}
