<?php
namespace show\data\modification\log;
use show\system\log\modification\EntryModificationLogHandler;
use wcf\data\modification\log\ModificationLogList;

/**
 * Represents a list of modification logs for entry list page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryListModificationLogList extends ModificationLogList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableEntryListEntryModificationLog::class;
	
	/**
	 * Initializes the entry list modification log list.
	 */
	public function setEntryData(array $entryIDs, $action = '') {
		$this->getConditionBuilder()->add("objectTypeID = ?", [EntryModificationLogHandler::getInstance()->getObjectType()->objectTypeID]);
		$this->getConditionBuilder()->add("objectID IN (?)", [$entryIDs]);
		if (!empty($action)) {
			$this->getConditionBuilder()->add("action = ?", [$action]);
		}
	}
}
