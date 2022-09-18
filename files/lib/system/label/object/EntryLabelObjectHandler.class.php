<?php
namespace show\system\label\object;
use show\system\cache\builder\ShowCategoryLabelCacheBuilder;
use wcf\system\label\object\AbstractLabelObjectHandler;
use wcf\system\label\LabelHandler;

/**
 * Label handler for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryLabelObjectHandler extends AbstractLabelObjectHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'com.uz.show.entry';
	
	/**
	 * Sets the label groups available for the categories with the given ids.
	 */
	public function setCategoryIDs($categoryIDs) {
		$labelGroupsToCategories = ShowCategoryLabelCacheBuilder::getInstance()->getData();
		
		$groupIDs = [];
		foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
			if (in_array($categoryID, $categoryIDs)) {
				$groupIDs = array_merge($groupIDs, $__groupIDs);
			}
		}
		
		$this->labelGroups = [];
		if (!empty($groupIDs)) {
			$this->labelGroups = LabelHandler::getInstance()->getLabelGroups(array_unique($groupIDs));
		}
	}
}
