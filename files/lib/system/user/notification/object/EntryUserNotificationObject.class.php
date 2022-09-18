<?php
namespace show\system\user\notification\object;
use show\data\entry\Entry;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\user\notification\object\IUserNotificationObject;

/**
 * Represents an entry as a notification object.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryUserNotificationObject extends DatabaseObjectDecorator implements IUserNotificationObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Entry::class;
	
	/**
	 * @inheritDoc
	 */
	public function getAuthorID() {
		return $this->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->entryID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getSubject();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getURL() {
		return $this->getLink();
	}
}
