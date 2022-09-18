<?php
namespace show\data\entry;
use wcf\data\tag\Tag;
use wcf\system\tagging\TagEngine;
use wcf\system\tagging\TTaggedObjectList;
use wcf\system\WCF;

/**
 * Represents a list of tagged entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class TaggedEntryList extends AccessibleEntryList {
	use TTaggedObjectList;
	
	/**
	 * tags
	 */
	public $tags;
	
	/**
	 * Creates a new TaggedEntryList object.
	 */
	public function __construct($tags) {
		parent::__construct();
		
		$this->tags = ($tags instanceof Tag) ? [$tags] : $tags;
		
		if (!WCF::getSession()->getPermission('user.show.canViewEntry')) {
			$this->getConditionBuilder()->add('1=0');
		}
		
		$this->getConditionBuilder()->add('tag_to_object.objectTypeID = ? AND tag_to_object.tagID IN (?)', [
				TagEngine::getInstance()->getObjectTypeID('com.uz.show.entry'),
				TagEngine::getInstance()->getTagIDs($this->tags),
		]);
		$this->getConditionBuilder()->add('entry.entryID = tag_to_object.objectID');
	}
	
	/**
	 * @inheritDoc
	 */
	public function countObjects() {
		$sql = "SELECT	COUNT(*)
				FROM	(
					SELECT	tag_to_object.objectID
					FROM	wcf".WCF_N."_tag_to_object tag_to_object,
							show".WCF_N."_entry entry
					".$this->sqlConditionJoins."
					".$this->getConditionBuilder()."
					GROUP BY tag_to_object.objectID
					HAVING COUNT(tag_to_object.objectID) = ?
			) AS t";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		$parameters = $this->getConditionBuilder()->getParameters();
		$parameters[] = count($this->tags);
		$statement->execute($parameters);
		
		return $statement->fetchSingleColumn();
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjectIDs() {
		$sql = "SELECT	tag_to_object.objectID
				FROM	wcf".WCF_N."_tag_to_object tag_to_object,
							show".WCF_N."_entry entry
				".$this->sqlConditionJoins."
				".$this->getConditionBuilder()."
				".$this->getGroupByFromOrderBy('tag_to_object.objectID', $this->sqlOrderBy)."
				HAVING COUNT(tag_to_object.objectID) = ?
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$statement = WCF::getDB()->prepareStatement($sql, $this->sqlLimit, $this->sqlOffset);
		
		$parameters = $this->getConditionBuilder()->getParameters();
		$parameters[] = count($this->tags);
		$statement->execute($parameters);
		$this->objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
	}
}
