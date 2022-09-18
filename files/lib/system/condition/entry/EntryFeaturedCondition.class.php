<?php
namespace show\system\condition\entry;
use show\data\entry\EntryList;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\AbstractCheckboxCondition;
use wcf\system\condition\IObjectListCondition;

/**
 * Condition implementation for entrys to only include featured entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryFeaturedCondition extends AbstractCheckboxCondition implements IObjectListCondition {
	/**
	 * @inheritDoc
	 */
	protected $fieldName = 'showEntryIsFeatured';
	
	/**
	 * @inheritDoc
	 */
	protected $label = 'show.entry.condition.isFeatured';
	
	/**
	 * @inheritDoc
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData) {
		if (!($objectList instanceof EntryList)) {
			throw new \InvalidArgumentException("Object list is no instance of '".EntryList::class."', instance of '".get_class($objectList)."' given.");
		}
		
		$objectList->getConditionBuilder()->add('entry.isFeatured = ?', [1]);
	}
}
