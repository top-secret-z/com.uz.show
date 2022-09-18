<?php
namespace show\system\user\activity\event;
use show\data\entry\ViewableEntryList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$objectIDs = [];
		foreach ($events as $event) {
			$objectIDs[] = $event->objectID;
		}
		
		// fetch entrys
		$entryList = new ViewableEntryList();
		$entryList->setObjectIDs($objectIDs);
		$entryList->readObjects();
		$entrys = $entryList->getObjects();
		
		// set message
		foreach ($events as $event) {
			if (isset($entrys[$event->objectID])) {
				if (!$entrys[$event->objectID]->canRead()) {
					continue;
				}
				$event->setIsAccessible();
				
				// title
				$text = WCF::getLanguage()->getDynamicVariable('show.entry.recentActivity.entry', ['entry' => $entrys[$event->objectID]]);
				$event->setTitle($text);
				
				// description
				$event->setDescription($entrys[$event->objectID]->getExcerpt());
			}
			else {
				$event->setIsOrphaned();
			}
		}
	}
}
