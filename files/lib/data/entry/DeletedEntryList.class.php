<?php
namespace show\data\entry;
use show\data\category\ShowCategory;
use wcf\system\clipboard\ClipboardHandler;

/**
 * Represents a list of deleted entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class DeletedEntryList extends ViewableEntryList {
	/**
	 * Creates a new DeletedEntryList object.
	 */
	public function __construct() {
		parent::__construct();
		
		// categories
		$accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
		if (!empty($accessibleCategoryIDs)) $this->getConditionBuilder()->add('entry.categoryID IN (?)', [$accessibleCategoryIDs]);
		else $this->getConditionBuilder()->add('1=0');
		
		$this->getConditionBuilder()->add('entry.isDeleted = ?', [1]);
	}
	
	/**
	 * Returns the number of marked items.
	 */
	public function getMarkedItems() {
		return ClipboardHandler::getInstance()->hasMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.uz.show.entry'));
	}
}
