<?php
namespace show\data\entry;
use wcf\data\like\object\AbstractLikeObject;
use wcf\data\like\Like;
use wcf\data\reaction\object\IReactionObject;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\object\LikeUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Likeable object implementation for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class LikeableEntry extends AbstractLikeObject implements IReactionObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Entry::class;
	
	/**
	 * @inheritDoc
	 */
	public function getLanguageID() {
		return $this->getDecoratedObject()->languageID;
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
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'object' => $this->getDecoratedObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getUserID() {
		return $this->userID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function sendNotification(Like $like) {
		if ($this->getDecoratedObject()->userID != WCF::getUser()->userID) {
			$notificationObject = new LikeUserNotificationObject($like);
			UserNotificationHandler::getInstance()->fireEvent(
				'like',
				'com.uz.show.likeableEntry.notification',
				$notificationObject,
				[$this->getDecoratedObject()->userID],
					['objectID' => $this->getDecoratedObject()->entryID]
			);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function updateLikeCounter($cumulativeLikes) {
		$editor = new EntryEditor($this->getDecoratedObject());
		$editor->update(['cumulativeLikes' => $cumulativeLikes]);
	}
}
