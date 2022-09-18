<?php
namespace show\system\event\listener;
use wcf\system\event\listener\IParameterizedEventListener;

/**
 * Adds show entries sort field to members list.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class MembersListPageListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		$eventObj->validSortFields[] = 'showEntrys';
	}
}
