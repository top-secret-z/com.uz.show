<?php
namespace show\system\user\notification\event;
use show\system\entry\EntryDataHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\request\LinkHandler;

/**
 * Notification event for poi categoriess.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCategoryUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return $this->getUserNotificationObject()->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEmailMessage($notificationType = 'instant') {
		return [
				'message-id' => 'com.uz.show.entry/'.$this->getUserNotificationObject()->entryID,
				'template' => 'email_notification_category',
				'application' => 'show'
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'object' => $this->getUserNotificationObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		return $this->getLanguage()->getDynamicVariable('show.entry.category.notification.message', [
				'entry' => $this->userNotificationObject,
				'author' => $this->author
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return $this->getLanguage()->get('show.entry.category.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		EntryDataHandler::getInstance()->cacheEntryID($this->getUserNotificationObject()->entryID);
	}
}
