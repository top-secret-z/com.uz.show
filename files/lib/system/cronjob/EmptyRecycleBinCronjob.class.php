<?php
namespace show\system\cronjob;
use show\data\entry\EntryAction;
use wcf\data\cronjob\Cronjob;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Deletes thrashed entries.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EmptyRecycleBinCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		if (SHOW_ENTRY_EMPTY_RECYCLE_BIN_CYCLE) {
			$sql = "SELECT	entryID
					FROM	show".WCF_N."_entry
					WHERE	isDeleted = ? AND deleteTime < ?";
			$statement = WCF::getDB()->prepareStatement($sql, 1000);
			$statement->execute([1, TIME_NOW - SHOW_ENTRY_EMPTY_RECYCLE_BIN_CYCLE * 86400]);
			$entryIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);
			
			if (!empty($entryIDs)) {
				$action = new EntryAction($entryIDs, 'delete');
				$action->executeAction();
			}
		}
	}
}
