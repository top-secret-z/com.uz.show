<?php
namespace show\system\log\modification;
use show\data\entry\Entry;
use show\data\entry\EntryList;
use show\data\modification\log\ViewableEntryModificationLog;
use wcf\data\label\Label;
use wcf\system\log\modification\AbstractExtendedModificationLogHandler;

/**
 * Handles entry modification logs.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryModificationLogHandler extends AbstractExtendedModificationLogHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.uz.show.entry';
	
	/**
	 * Adds an entry modification log entry.
	 */
	public function add(Entry $entry, $action, array $additionalData = []) {
		$this->createLog($action, $entry->entryID, null, $additionalData);
	}
	
	/**
	 * Adds a log entry for entry delete.
	 */
	public function delete(Entry $entry) {
		$this->add($entry, 'delete', ['time' => $entry->time, 'subject' => $entry->getSubject()]);
	}
	
	/**
	 * Adds a log entry for entry disable.
	 */
	public function disable(Entry $entry) {
		$this->add($entry, 'disable');
	}
	
	/**
	 * Adds a log entry for entry edit.
	 */
	public function edit(Entry $entry, $reason = '') {
		$this->add($entry, 'edit', ['reason' => $reason]);
	}
	
	/**
	 * Adds a log entry for entry enable.
	 */
	public function enable(Entry $entry) {
		$this->add($entry, 'enable');
	}
	
	/**
	 * Adds a log entry for entry restore.
	 */
	public function restore(Entry $entry) {
		$this->add($entry, 'restore');
	}
	
	/**
	 * Adds a log entry for changed labels.
	 */
	public function setLabel(Entry $entry, Label $label) {
		$this->add($entry, 'setLabel', ['label' => $label]);
	}
	
	/**
	 * Adds a log entry for entry soft-delete (trash).
	 */
	public function trash(Entry $entry, $reason = '') {
		$this->add($entry, 'trash', ['reason' => $reason]);
	}
	
	/**
	 * Adds a log entry for entry setAsFeatured / unsetAsFeatured.
	 */
	public function setAsFeatured(Entry $entry) {
		$this->add($entry, 'setAsFeatured');
	}
	
	public function unsetAsFeatured(Entry $entry) {
		$this->add($entry, 'unsetAsFeatured');
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAvailableActions() {
		return ['delete', 'disable', 'edit', 'enable', 'restore', 'setAsFeatured', 'trash', 'unsetAsFeatured', 'setLabel'];
	}
	
	/**
	 * @inheritDoc
	 */
	public function processItems(array $items) {
		$entryIDs = [];
		foreach ($items as &$item) {
			$entryIDs[] = $item->objectID;
			
			$item = new ViewableEntryModificationLog($item);
		}
		unset($item);
		
		if (!empty($entryIDs)) {
			$entryList = new EntryList();
			$entryList->setObjectIDs($entryIDs);
			$entryList->readObjects();
			$entrys = $entryList->getObjects();
			
			foreach ($items as $item) {
				if (isset($entrys[$item->objectID])) {
					$item->setEntry($entrys[$item->objectID]);
				}
			}
		}
		
		return $items;
	}
}
