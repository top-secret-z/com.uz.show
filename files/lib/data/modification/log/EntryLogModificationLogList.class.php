<?php
namespace show\data\modification\log;
use show\data\entry\Entry;
use show\system\log\modification\EntryModificationLogHandler;
use wcf\data\modification\log\ModificationLogList;
use wcf\system\WCF;

/**
 * Represents a list of modification logs for entry log page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryLogModificationLogList extends ModificationLogList {
	/**
	 * entry data
	 */
	public $entryObjectTypeID = 0;
	public $entry;
	
	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		
		// get object types
		$this->entryObjectTypeID = EntryModificationLogHandler::getInstance()->getObjectType()->objectTypeID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		$sql = "SELECT		COUNT(modification_log.logID) AS count
				FROM		wcf".WCF_N."_modification_log modification_log
				WHERE		modification_log.objectTypeID = ? AND modification_log.objectID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->entryObjectTypeID, $this->entry->entryID]);
		$count = 0;
		while ($row = $statement->fetchArray()) {
			$count += $row['count'];
		}
		
		return $count;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		$sql = "SELECT		user_avatar.*, user_table.email, user_table.disableAvatar, user_table.enableGravatar, user_table.gravatarFileExtension, modification_log.*
				FROM		wcf".WCF_N."_modification_log modification_log
				LEFT JOIN	wcf".WCF_N."_user user_table
				ON			(user_table.userID = modification_log.userID)
				LEFT JOIN	wcf".WCF_N."_user_avatar user_avatar
				ON			(user_avatar.avatarID = user_table.avatarID)
				WHERE		modification_log.objectTypeID = ? AND modification_log.objectID = ?
				".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
		$statement->execute([$this->entryObjectTypeID, $this->entry->entryID]);
		$this->objects = $statement->fetchObjects(($this->objectClassName ?: $this->className));
		
		// use table index as array index
		$objects = [];
		foreach ($this->objects as $object) {
			$objectID = $object->{$this->getDatabaseTableIndexName()};
			$objects[$objectID] = $object;
			
			$this->indexToObject[] = $objectID;
		}
		$this->objectIDs = $this->indexToObject;
		$this->objects = $objects;
		
		$versionIDs = [];
		foreach ($this->objects as &$object) {
			$object = new ViewableEntryModificationLog($object);
		}
		unset($object);
	}
	
	/**
	 * Initializes the entry's log.
	 */
	public function setEntry(Entry $entry) {
		$this->entry = $entry;
	}
}
