<?php
namespace show\system\condition\entry;
use show\data\entry\EntryList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractMultiCategoryCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for the category an entry belongs to.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCategoryCondition extends AbstractMultiCategoryCondition implements IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'entryCategoryIDs';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'show.entry.category';
	
	/**
	 * @inheritDoc
	 */
	public $objectType = 'com.uz.show.category';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof EntryList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".EntryList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('entry.categoryID IN (?)', [$conditionData[$this->fieldName]]);
	}
}
