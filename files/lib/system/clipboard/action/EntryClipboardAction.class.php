<?php
namespace show\system\clipboard\action;
use show\data\category\ShowCategory;
use show\data\entry\Entry;
use show\data\entry\EntryAction;
use wcf\data\clipboard\action\ClipboardAction;
use wcf\system\clipboard\action\AbstractClipboardAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\label\LabelHandler;
use wcf\system\WCF;

/**
 * Prepares clipboard editor items for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryClipboardAction extends AbstractClipboardAction {
	/**
	 * @inheritDoc
	 */
	protected $actionClassActions = ['delete', 'enable', 'disable', 'restore', 'trash'];
	
	/**
	 * list of active entry objects
	 */
	protected $entrys = [];
	
	/**
	 * list of availebl labels groups
	 */
	protected $labelGroups = [];
	
	/**
	 * @inheritDoc
	 */
	protected $supportedActions = ['assignLabel', 'delete', 'enable', 'disable', 'restore', 'trash'];
	
	/**
	 * @inheritDoc
	 */
	public function execute(array $objects, ClipboardAction $action) {
		$this->entrys = $objects;
		
		$item = parent::execute($objects, $action);
		if ($item === null) {
			return null;
		}
		
		// handle actions
		switch ($action->actionName) {
			case 'assignLabel':
				// only offer label assignment on category pages
				if (!ClipboardHandler::getInstance()->getPageObjectID()) {
					return null;
				}
				
				$item->addParameter('categoryID', ClipboardHandler::getInstance()->getPageObjectID());
				$item->addParameter('template', WCF::getTPL()->fetch('entryAssignLabel', 'show', [
					'labelGroups' => $this->labelGroups
				]));
			break;
			
			case 'trash':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.show.entry.trash.confirmMessage', [
					'count' => $item->getCount()
				]));
				$item->addInternalData('template', WCF::getTPL()->fetch('entryDeleteReason', 'show'));
			break;
			
			case 'delete':
				$item->addInternalData('confirmMessage', WCF::getLanguage()->getDynamicVariable('wcf.clipboard.item.com.uz.show.entry.delete.confirmMessage', [
					'count' => $item->getCount()
				]));
			break;
		}
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return EntryAction::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTypeName() {
		return 'com.uz.show.entry';
	}
	
	/**
	 * Validates entrys available for label assignment.
	 */
	public function validateAssignLabel() {
		$entryIDs = [];
		
		$categoryID = ClipboardHandler::getInstance()->getPageObjectID();
		$category = ShowCategory::getCategory($categoryID);
		if ($category === null || $category->getObjectType()->objectType != ShowCategory::OBJECT_TYPE_NAME) {
			return $entryIDs;
		}
		
		$labelGroupIDs = array_keys($category->getLabelGroups());
		if (!empty($labelGroupIDs)) {
			$this->labelGroups = LabelHandler::getInstance()->getLabelGroups($labelGroupIDs, true, 'canSetLabel');
			if (!empty($this->labelGroups)) {
				foreach ($this->objects as $entry) {
					if ($category->categoryID === $entry->categoryID) {
						$entryIDs[] = $entry->entryID;
					}
				}
			}
		}
		
		return $entryIDs;
	}
	
	/**
	 * Validates entrys valid for deleting and returns their ids.
	 */
	public function validateDelete() {
		$entryIDs = [];
		
		foreach ($this->entrys as $entry) {
			if ($entry->isDeleted && WCF::getSession()->getPermission('mod.show.canDeleteEntryCompletely')) {
				$entryIDs[] = $entry->entryID;
			}
		}
		
		return $entryIDs;
	}
	
	/**
	 * Validates entrys valid for disabling and returns their ids.
	 */
	public function validateDisable() {
		$entryIDs = [];
		
		foreach ($this->entrys as $entry) {
			if (!$entry->isDisabled && !$entry->isDeleted && WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
				$entryIDs[] = $entry->entryID;
			}
		}
		
		return $entryIDs;
	}
	
	/**
	 * Validates entrys valid for enabling and returns their ids.
	 */
	public function validateEnable() {
		$entryIDs = [];
		
		foreach ($this->entrys as $entry) {
			if ($entry->isDisabled && !$entry->isDeleted && WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
				$entryIDs[] = $entry->entryID;
			}
		}
		
		return $entryIDs;
	}
	
	/**
	 * Validates entrys valid for restoring and returns their ids.
	 */
	public function validateRestore() {
		$entryIDs = [];
		
		foreach ($this->entrys as $entry) {
			if ($entry->isDeleted && WCF::getSession()->getPermission('mod.show.canRestoreEntry')) {
				$entryIDs[] = $entry->entryID;
			}
		}
		
		return $entryIDs;
	}
	
	/**
	 * Validates entrys valid for trashing and returns their ids.
	 */
	public function validateTrash() {
		$entryIDs = [];
		
		foreach ($this->entrys as $entry) {
			if (!$entry->isDeleted && WCF::getSession()->getPermission('mod.show.canDeleteEntry')) {
				$entryIDs[] = $entry->entryID;
			}
		}
		
		return $entryIDs;
	}
}
