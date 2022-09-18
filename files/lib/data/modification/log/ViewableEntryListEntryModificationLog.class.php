<?php
namespace show\data\modification\log;
use wcf\data\modification\log\ModificationLog;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Provides a viewable entry modification log within entry list page.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ViewableEntryListEntryModificationLog extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModificationLog::class;
	
	/**
	 * Returns readable representation of current log entry.
	 */
	public function __toString() {
		return WCF::getLanguage()->getDynamicVariable('show.entry.log.entry.'.$this->action.'.summary', [
				'additionalData' => $this->additionalData,
				'time' => $this->time,
				'userID' => $this->userID,
				'username' => $this->username
		]);
	}
}
