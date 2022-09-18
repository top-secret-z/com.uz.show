<?php
namespace show\data\modification\log;
use show\data\entry\Entry;
use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLog;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\WCF;

/**
 * Provides a viewable entry modification log.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ViewableEntryModificationLog extends DatabaseObjectDecorator implements IViewableModificationLog {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModificationLog::class;
	
	/**
	 * Entry this modification log belongs to
	 */
	protected $entry;
	
	/**
	 * user profile object
	 */
	protected $userProfile;
	
	/**
	 * Returns readable representation of current log entry.
	 */
	public function __toString() {
		return WCF::getLanguage()->getDynamicVariable('show.entry.log.entry.'.$this->action, ['additionalData' => $this->additionalData]);
	}
	
	/**
	 * Returns the user profile object.
	 */
	public function getUserProfile() {
		if ($this->userProfile === null) {
			$this->userProfile = new UserProfile(new User(null, $this->getDecoratedObject()->data));
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Sets the entry this modification log belongs to.
	 */
	public function setEntry(Entry $entry) {
		if ($entry->entryID == $this->objectID) {
			$this->entry = $entry;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getAffectedObject() {
		return $this->entry;
	}
}
