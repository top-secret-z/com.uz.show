<?php
namespace show\system\user\notification\event;
use show\system\entry\EntryDataHandler;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;
use wcf\system\user\notification\event\TReactionUserNotificationEvent;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * User notification event for entry comment response likes.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentResponseLikeUserNotificationEvent extends AbstractSharedUserNotificationEvent  {
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
	public function getEmailMessage($notificationType = 'instant') { /* not supported */ }
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'object' => $entry
		], '#comments/comment' . $this->additionalData['commentID'] . '/response' . $this->getUserNotificationObject()->objectID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);
		$authors = array_values($this->getAuthors());
		$count = count($authors);
		$commentUser = null;
		if ($this->additionalData['commentUserID'] != WCF::getUser()->userID) {
			$commentUser = UserProfileRuntimeCache::getInstance()->getObject($this->additionalData['commentUserID']);
		}
		
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('show.entry.commentResponse.like.notification.message.stacked', [
					'author' => $this->author,
					'authors' => $authors,
					'commentID' => $this->additionalData['commentID'],
					'commentUser' => $commentUser,
					'count' => $count,
					'others' => $count - 1,
					'entry' => $entry,
					'responseID' => $this->getUserNotificationObject()->objectID,
					'reactions' => $this->getReactionsForAuthors()
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('show.entry.commentResponse.like.notification.message', [
				'author' => $this->author,
				'commentID' => $this->additionalData['commentID'],
				'entry' => $entry,
				'responseID' => $this->getUserNotificationObject()->objectID,
				'reactions' => $this->getReactionsForAuthors()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('show.entry.commentResponse.like.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('show.entry.commentResponse.like.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		EntryDataHandler::getInstance()->cacheEntryID($this->additionalData['objectID']);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['commentUserID']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsEmailNotification() {
		return false;
	}
}
