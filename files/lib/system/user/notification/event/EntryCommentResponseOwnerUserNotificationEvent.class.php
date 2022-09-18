<?php
namespace show\system\user\notification\event;
use show\system\entry\EntryDataHandler;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\email\Email;
use wcf\system\request\LinkHandler;
use wcf\system\user\notification\event\AbstractSharedUserNotificationEvent;

/**
 * User notification event for entry owner for comment responses.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentResponseOwnerUserNotificationEvent extends AbstractSharedUserNotificationEvent {
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
	public function getEmailMessage($notificationType = 'instant') {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		$entry = EntryDataHandler::getInstance()->getEntry($comment->objectID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		
		$messageID = '<com.uz.show.entry.comment/'.$comment->commentID.'@'.Email::getHost().'>';
		
		return [
				'template' => 'email_notification_commentResponseOwner',
				'application' => 'wcf',
				'in-reply-to' => [$messageID],
				'references' => [$messageID],
				'variables' => [
						'commentAuthor' => $commentAuthor,
						'commentID' => $this->getUserNotificationObject()->commentID,
						'entry' => $entry,
						'responseID' => $this->getUserNotificationObject()->responseID,
						'languageVariablePrefix' => 'show.entry.commentResponseOwner.notification'
				]
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEventHash() {
		return sha1($this->eventID . '-' . $this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		$entry = EntryDataHandler::getInstance()->getEntry($this->additionalData['objectID']);
		
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'object' => $entry
		], '#comments/comment' . $this->getUserNotificationObject()->commentID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getMessage() {
		$comment = CommentRuntimeCache::getInstance()->getObject($this->getUserNotificationObject()->commentID);
		if ($comment->userID) {
			$commentAuthor = UserProfileRuntimeCache::getInstance()->getObject($comment->userID);
		}
		else {
			$commentAuthor = UserProfile::getGuestUserProfile($comment->username);
		}
		$entry = EntryDataHandler::getInstance()->getEntry($comment->objectID);
		
		$authors = $this->getAuthors();
		if (count($authors) > 1) {
			if (isset($authors[0])) {
				unset($authors[0]);
			}
			$count = count($authors);
			
			return $this->getLanguage()->getDynamicVariable('show.entry.commentResponseOwner.notification.message.stacked', [
					'author' => $commentAuthor,
					'authors' => array_values($authors),
					'commentID' => $this->getUserNotificationObject()->commentID,
					'count' => $count,
					'entry' => $entry,
					'others' => $count - 1,
					'guestTimesTriggered' => $this->notification->guestTimesTriggered
			]);
		}
		
		return $this->getLanguage()->getDynamicVariable('show.entry.commentResponseOwner.notification.message', [
				'entry' => $entry,
				'author' => $this->author,
				'commentAuthor' => $commentAuthor,
				'commentID' => $this->getUserNotificationObject()->commentID,
				'responseID' => $this->getUserNotificationObject()->responseID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		$count = count($this->getAuthors());
		if ($count > 1) {
			return $this->getLanguage()->getDynamicVariable('show.entry.commentResponseOwner.notification.title.stacked', [
					'count' => $count,
					'timesTriggered' => $this->notification->timesTriggered
			]);
		}
		
		return $this->getLanguage()->get('show.entry.commentResponseOwner.notification.title');
	}
	
	/**
	 * @inheritDoc
	 */
	protected function prepare() {
		EntryDataHandler::getInstance()->cacheEntryID($this->additionalData['objectID']);
		CommentRuntimeCache::getInstance()->cacheObjectID($this->getUserNotificationObject()->commentID);
		UserProfileRuntimeCache::getInstance()->cacheObjectID($this->additionalData['userID']);
	}
}
