<?php
namespace show\system\page\handler;
use show\data\entry\ViewableEntry;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\WCF;

/**
 * Menu page handler for list of unread entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UnreadEntryListPagePageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		return ViewableEntry::getUnreadEntrys();
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		return (WCF::getUser()->userID != 0 && ViewableEntry::getUnreadEntrys());
	}
}
