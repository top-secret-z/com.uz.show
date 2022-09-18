<?php
namespace show\system\moderation\queue;
use show\data\entry\Entry;
use show\data\entry\EntryAction;
use show\data\entry\EntryList;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\moderation\queue\AbstractModerationQueueHandler;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * An abstract implementation of IModerationQueueHandler for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
abstract class AbstractEntryModerationQueueHandler extends AbstractModerationQueueHandler {
	/**
	 * @inheritDoc
	 */
	protected $className = Entry::class;
	
	/**
	 * list of entry objects
	 */
	protected static $entrys = [];
	
	/**
	 * @inheritDoc
	 */
	protected $requiredPermission = 'mod.show.canModerateEntry';
	
	/**
	 * @inheritDoc
	 */
	public function assignQueues(array $queues) {
		$assignments = [];
		foreach ($queues as $queue) {
			$assignUser = false;
			if (WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
				$assignUser = true;
			}
			
			$assignments[$queue->queueID] = $assignUser;
		}
		
		ModerationQueueManager::getInstance()->setAssignment($assignments);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getContainerID($objectID) {
		return 0;
	}
	
	/**
	 * Returns an entry object by entry id or null if entry id is invalid.
	 */
	protected function getEntry($objectID) {
		if (!array_key_exists($objectID, self::$entrys)) {
			self::$entrys[$objectID] = new Entry($objectID);
			if (!self::$entrys[$objectID]->entryID) {
				self::$entrys[$objectID] = null;
			}
		}
		
		return self::$entrys[$objectID];
	}
	
	/**
	 * @inheritDoc
	 */
	public function isValid($objectID) {
		if ($this->getEntry($objectID) === null) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate(array $queues) {
		$objectIDs = [];
		foreach ($queues as $object) {
			$objectIDs[] = $object->objectID;
		}
		
		// fetch entrys
		$entryList = new EntryList();
		$entryList->setObjectIDs($objectIDs);
		$entryList->readObjects();
		$entrys = $entryList->getObjects();
		
		foreach ($queues as $object) {
			if (isset($entrys[$object->objectID])) {
				$object->setAffectedObject($entrys[$object->objectID]);
			}
			else {
				$object->setIsOrphaned();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeContent(ModerationQueue $queue, $message) {
		if ($this->isValid($queue->objectID) && !$this->getEntry($queue->objectID)->isDeleted) {
			$action = new EntryAction([$this->getEntry($queue->objectID)], 'trash', ['data' => ['reason' => $message]]);
			$action->executeAction();
		}
	}
}
