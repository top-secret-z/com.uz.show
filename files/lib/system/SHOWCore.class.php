<?php
namespace show\system;
use show\data\category\ShowCategory;
use show\data\entry\Entry;
use show\page\EntryListPage;
use wcf\system\application\AbstractApplication;
use wcf\system\page\PageLocationManager;

/**
 * This class extends the main WCF class by show specific functions.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class SHOWCore extends AbstractApplication {
	/**
	 * @inheritDoc
	 */
	protected $primaryController = EntryListPage::class;
	
	/**
	 * Sets location data.
	 */
	public function setLocation(array $parentCategories = [], ShowCategory $category = null, Entry $entry = null) {
		// add entry
		if ($entry !== null) {
			PageLocationManager::getInstance()->addParentLocation('com.uz.show.Entry', $entry->entryID, $entry);
		}
		
		// add category
		if ($category !== null) {
			PageLocationManager::getInstance()->addParentLocation('com.uz.show.CategoryEntryList', $category->categoryID, $category, true);
		}
		
		// add parent categories
		$parentCategories = array_reverse($parentCategories);
		foreach ($parentCategories as $parentCategory) {
			PageLocationManager::getInstance()->addParentLocation('com.uz.show.CategoryEntryList', $parentCategory->categoryID, $parentCategory);
		}
	}
}
