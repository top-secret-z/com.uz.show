<?php
namespace show\system\event\listener;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\worker\UserRebuildDataWorker;
use wcf\system\WCF;

/**
 * Updates users' entry counter.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class UserRebuildDataWorkerListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		/** @var UserRebuildDataWorker $eventObj */
		
		$userIDs = [];
		foreach ($eventObj->getObjectList() as $user) {
			$userIDs[] = $user->userID;
		}
		
		if (!empty($userIDs)) {
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add('user_table.userID IN (?)', [$userIDs]);
			$sql = "UPDATE	wcf".WCF_N."_user user_table
					SET		showEntrys = (SELECT	COUNT(*) FROM	show".WCF_N."_entry entry WHERE entry.userID = user_table.userID AND entry.isDisabled = 0)
					".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditionBuilder->getParameters());
		}
	}
}
