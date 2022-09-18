<?php
namespace show\data\entry;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Entry::class;
	
	/**
	 * Updates the entry counter of the given users.
	 */
	public static function updateEntryCounter(array $users) {
		$sql = "UPDATE	wcf".WCF_N."_user
				SET		showEntrys = showEntrys + ?
				WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($users as $userID => $entrys) {
			$statement->execute([$entrys, $userID]);
		}
	}
	
	/**
	 * Updates additional categories (IDs).
	 */
	public function updateCategories(array $categoryIDs = []) {
		// remove old first
		$sql = "DELETE FROM	show".WCF_N."_entry_to_category
				WHERE		entryID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->entryID]);
		
		// add new categories, if configured
		// always add main category
		if (!in_array($this->categoryID, $categoryIDs)) $categoryIDs[] = $this->categoryID;
		
		if (!empty($categoryIDs) && SHOW_CATEGORY_ENABLE) {
			WCF::getDB()->beginTransaction();
			
			$sql = "INSERT IGNORE INTO	show".WCF_N."_entry_to_category
						(categoryID, entryID)
					VALUES		(?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($categoryIDs as $categoryID) {
				$statement->execute([$categoryID, $this->entryID]);
			}
			WCF::getDB()->commitTransaction();
		}
	}
}
