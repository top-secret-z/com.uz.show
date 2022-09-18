<?php
namespace show\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds support for sorting by entry count to the user list box controller.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UserListBoxControllerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		switch ($eventName) {
			case '__construct':
				$eventObj->validSortFields[] = 'showEntrys';
				break;
			
			case 'readObjects':
				// only consider users with at least one entry
				if ($eventObj->sortField === 'showEntrys') {
					$eventObj->objectList->getConditionBuilder()->add('user_table.showEntrys > 0');
				}
				break;
			
			default:
				throw new \InvalidArgumentException("Cannot handle event '{$eventName}'");
		}
	}
}
