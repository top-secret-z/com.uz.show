<?php
namespace show\data\entry;
use show\system\SHOWCore;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\TUserContent;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\WCF;

/**
 * History saving point implementation for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class HistorySavingEntry extends DatabaseObjectDecorator implements IHistorySavingObject {
	use TUserContent;
	
	/**
	 * last edit data
	 */
	public $reason = '';
	public $time = 0;
	public $userID = 0;
	public $username = '';
	
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Entry::class;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(DatabaseObject $object) {
		parent::__construct($object);
		
		// fetch the data of the latest edit
		$objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.modifiableContent', 'com.uz.show.entry');
		
		$sql = "SELECT	*
				FROM		wcf".WCF_N."_modification_log
				WHERE		objectTypeID = ? AND objectID = ? AND action = ?
				ORDER BY 	time DESC";
		$statement = WCF::getDB()->prepareStatement($sql, 1);
		$statement->execute([$objectTypeID, $this->getDecoratedObject()->entryID, 'edit']);
		$row = $statement->fetchSingleRow();
		
		if ($row) {
			$this->userID = $row['userID'];
			$this->username = $row['username'];
			$this->time = $row['time'];
			$additionalData = @unserialize($row['additionalData']);
			if (isset($additionalData['reason'])) {
				$this->reason = $additionalData['reason'];
			}
			else {
				$this->reason = '';
			}
		}
		else {
			$this->userID = $this->getDecoratedObject()->getUserID();
			$this->username = $this->getDecoratedObject()->getUsername();
			$this->time = $this->getDecoratedObject()->getTime();
			$this->reason = '';
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEditReason() {
		return $this->reason;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return $this->getDecoratedObject()->getLink();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getDecoratedObject()->getMessage();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLocation() {
		if (!SHOW_CATEGORY_ENABLE) {
			SHOWCore::getInstance()->setLocation($this->getCategory()->getParentCategories(), $this->getCategory(), $this->getDecoratedObject());
		}
		else {
			SHOWCore::getInstance()->setLocation();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getDecoratedObject()->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function revertVersion(EditHistoryEntry $edit) {
		$entryAction = new EntryAction([$this->getDecoratedObject()], 'update', [
				'isEdit' => true,
				'data' => [
						'message' => $edit->message
				],
				'editReason' => WCF::getLanguage()->getDynamicVariable('wcf.edit.reverted', ['edit' => $edit])
		]);
		$entryAction->executeAction();
	}
}
