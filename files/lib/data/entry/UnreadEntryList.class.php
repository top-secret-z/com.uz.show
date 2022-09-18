<?php
namespace show\data\entry;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Represents a list of unread entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UnreadEntryList extends AccessibleEntryList {
	/**
	 * Creates a new UnreadEntryList object.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->sqlConditionJoins .= " LEFT JOIN wcf".WCF_N."_tracked_visit tracked_visit ON (tracked_visit.objectTypeID = ".VisitTracker::getInstance()->getObjectTypeID('com.uz.show.entry')." AND tracked_visit.objectID = entry.entryID AND tracked_visit.userID = ".WCF::getUser()->userID.")";
		if (SHOW_LAST_CHANGE_NEW) {
			$this->getConditionBuilder()->add('entry.lastChangeTime > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
			$this->getConditionBuilder()->add('(entry.lastChangeTime > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');
		}
		else {
			$this->getConditionBuilder()->add('entry.time > ?', [VisitTracker::getInstance()->getVisitTime('com.uz.show.entry')]);
			$this->getConditionBuilder()->add('(entry.time > tracked_visit.visitTime OR tracked_visit.visitTime IS NULL)');
		}
	}
}
