<?php
namespace show\data\category;
use wcf\data\category\CategoryEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Executes show category-related actions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = CategoryEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['markAllAsRead'];
	
	/**
	 * Validates the mark all as read action.
	 */
	public function validateMarkAllAsRead() {
		// nothing ufn
	}
	
	/**
	 * Marks all categories as read.
	 */
	public function markAllAsRead() {
		VisitTracker::getInstance()->trackTypeVisit('com.uz.show.entry');
		
		// reset storage and notifications
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'showUnreadEntrys');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'showUnreadWatchedEntrys');
		}
	}
}
