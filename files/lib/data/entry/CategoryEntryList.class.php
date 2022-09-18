<?php
namespace show\data\entry;
use show\data\category\ShowCategory;
use wcf\system\exception\SystemException;

/**
 * Represents a list of entrys in specific categories.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryEntryList extends AccessibleEntryList {
	/**
	 * @inheritDoc
	 */
	protected $applyCategoryFilter = false;
	
	/**
	 * Creates a new CategoryEntryList object.
	 */
	public function __construct($categoryID, $includeChildCategories = false) {
		parent::__construct();
		
		$categoryIDs = [$categoryID];
		
		if ($includeChildCategories) {
			$category = ShowCategory::getCategory($categoryID);
			
			if ($category === null) {
				throw new SystemException("invalid category id '".$categoryID."' given");
			}
			foreach ($category->getAllChildCategories() as $category) {
				if ($category->isAccessible()) {
					$categoryIDs[] = $category->categoryID;
				}
			}
		}
		
		if (!SHOW_CATEGORY_ENABLE) {
			$this->getConditionBuilder()->add('entry.categoryID IN (?)', [$categoryIDs]);
		}
		
		// add addtitional categories
		if (SHOW_CATEGORY_ENABLE) {
			$this->getConditionBuilder()->add('(entry.categoryID IN (?) OR entry.entryID IN (SELECT entryID FROM show'.WCF_N.'_entry_to_category WHERE categoryID IN (?)))', [$categoryIDs, $categoryIDs]);
		}
	}
}
