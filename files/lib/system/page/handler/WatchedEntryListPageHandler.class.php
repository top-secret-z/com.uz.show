<?php
namespace show\system\page\handler;
use show\data\category\ShowCategory;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\page\handler\AbstractMenuPageHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Menu page handler for the watched entrys page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class WatchedEntryListPageHandler extends AbstractMenuPageHandler {
	/**
	 * number of unread entrys
	 */
	protected $notifications;
	
	/**
	 * @inheritDoc
	 */
	public function getOutstandingItemCount($objectID = null) {
		if ($this->notifications === null) {
			$this->notifications = 0;
			
			if (WCF::getUser()->userID) {
				$data = UserStorageHandler::getInstance()->getField('showUnreadWatchedEntrys');
				
				// cache does not exist or is outdated
				if ($data === null) {
					$categoryIDs = ShowCategory::getAccessibleCategoryIDs();
					if (!empty($categoryIDs)) {
						$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.entry');
						
						$conditionBuilder = new PreparedStatementConditionBuilder();
						$conditionBuilder->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
						$conditionBuilder->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
						$conditionBuilder->add('entry.categoryID IN (?)', [$categoryIDs]);
						$conditionBuilder->add('entry.isDeleted = 0 AND entry.isDisabled = 0');
						if (SHOW_LAST_CHANGE_NEW) {
							$conditionBuilder->add('entry.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
							$conditionBuilder->add('(entry.lastChangeTime > tracked_entry_visit.visitTime OR tracked_entry_visit.visitTime IS NULL)');
						}
						else {
							$conditionBuilder->add('entry.time > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
							$conditionBuilder->add('(entry.time > tracked_entry_visit.visitTime OR tracked_entry_visit.visitTime IS NULL)');
						}
						
						$sql = "SELECT		COUNT(*)
								FROM		wcf".WCF_N."_user_object_watch user_object_watch
								LEFT JOIN	show".WCF_N."_entry entry
								ON			(entry.entryID = user_object_watch.objectID)
								LEFT JOIN	wcf".WCF_N."_tracked_visit tracked_entry_visit
								ON			(tracked_entry_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry')." AND tracked_entry_visit.objectID = entry.entryID AND tracked_entry_visit.userID = ".WCF::getUser()->userID.")
								".$conditionBuilder;
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute($conditionBuilder->getParameters());
						$this->notifications = $statement->fetchSingleColumn();
					}
					
					// update storage data
					UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'showUnreadWatchedEntrys', $this->notifications);
				}
				else {
					$this->notifications = $data;
				}
			}
		}
		
		return $this->notifications;
	}
	
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		$count = 0;
		if (WCF::getUser()->userID) {
			$data = UserStorageHandler::getInstance()->getField('showWatchedEntrys');
			
			// cache does not exist or is outdated
			if ($data === null) {
				$categoryIDs = ShowCategory::getAccessibleCategoryIDs();
				if (!empty($categoryIDs)) {
					$objectTypeID = UserObjectWatchHandler::getInstance()->getObjectTypeID('com.uz.show.entry');
					
					$conditionBuilder = new PreparedStatementConditionBuilder();
					$conditionBuilder->add('user_object_watch.objectTypeID = ?', [$objectTypeID]);
					$conditionBuilder->add('user_object_watch.userID = ?', [WCF::getUser()->userID]);
					$conditionBuilder->add('entry.categoryID IN (?)', [$categoryIDs]);
					$conditionBuilder->add('entry.isDeleted = 0 AND entry.isDisabled = 0');
					
					$sql = "SELECT		COUNT(*)
							FROM		wcf".WCF_N."_user_object_watch user_object_watch
							LEFT JOIN	show".WCF_N."_entry entry
							ON			(entry.entryID = user_object_watch.objectID)
							".$conditionBuilder;
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute($conditionBuilder->getParameters());
					$count = $statement->fetchSingleColumn();
				}
				
				// update storage data
				UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'showWatchedEntrys', $count);
			}
			else {
				$count = $data;
			}
		}
		
		return ($count != 0);
	}
}
