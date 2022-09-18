<?php
namespace show\system\user\notification\event;
use show\system\entry\EntryDataHandler;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;

/**
 * User notification event for entry likes.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent {
	use TReactionUserNotificationEvent;
	
	/**
	 * @inheritDoc
	 */
	protected $stackable = true;
	
	/**
	 * @inheritDoc
	 */
	public function checkAccess() {
		return EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID'])->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'object' => $entry
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('show.entry.like.notification.message.stacked', [
					'author' => $this->author,
					'authors' => $authors,
					'count' => $count,
					'others' => $count - 1,
					'entry' => $entry,
					'reactions' => $this->getReactionsForAuthors()
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('show.entry.like.notification.message', [
			'author' => $this->author,
				'entry' => $entry,
				'reactions' => $this->getReactionsForAuthors()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('show.entry.like.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('show.entry.like.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		EntryDataHandler::getInstance()->cacheEntryID($this->additionalData['objectID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
}
