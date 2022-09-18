<?php
namespace show\system\comment\manager;
use show\data\entry\Entry;
use show\data\entry\EntryEditor;
use show\data\entry\ViewableEntryList;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\comment\manager\AbstractCommentManager;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Show comment manager implementation.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentManager extends AbstractCommentManager implements IViewableLikeProvider {
	/**
	 * @inheritdoc
	 */
	protected $permissionAdd = 'user.show.canAddComment';
	protected $permissionAddWithoutModeration = 'user.show.canAddCommentWithoutModeration';
	protected $permissionCanModerate = 'mod.show.canModerateComment';
	protected $permissionDelete = 'user.show.canDeleteComment';
	protected $permissionEdit = 'user.show.canEditComment';
	protected $permissionModDelete = 'mod.show.canDeleteComment';
	protected $permissionModEdit = 'mod.show.canEditComment';
	
	/**
	 * @inheritDoc
	 */
	public function getCommentLink(Comment $comment) {
		return $this->getLink($comment->objectTypeID, $comment->objectID) . '#comments/comment' . $comment->commentID;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getLink($objectTypeID, $objectID) {
		return LinkHandler::getInstance()->getLink('Entry', [
				'application' => 'show',
				'id' => $objectID
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getResponseLink(CommentResponse $response) {
		return $this->getLink($response->getComment()->objectTypeID, $response->getComment()->objectID)
			. '#comments/comment' . $response->commentID . '/response' . $response->responseID;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getTitle($objectTypeID, $objectID, $isResponse = false) {
		if ($isResponse) return WCF::getLanguage()->get('show.entry.commentResponse');
		
		return WCF::getLanguage()->getDynamicVariable('show.entry.comment');
	}
	
	/**
	 * @inheritdoc
	 */
	public function isAccessible($objectID, $validateWritePermission = false) {
		$entry = new Entry($objectID);
		if (!$entry->entryID || !$entry->canRead()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * @inheritdoc
	 */
	public function prepare(array $likes) {
		if (!WCF::getSession()->getPermission('user.show.canViewEntry')) {
			return;
		}
		
		$commentLikeObjectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment');
		
		$commentIDs = $responseIDs = [];
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				$commentIDs[] = $like->objectID;
			}
			else {
				$responseIDs[] = $like->objectID;
			}
		}
		
		// fetch response
		$userIDs = $responses = [];
		if (!empty($responseIDs)) {
			$responseList = new CommentResponseList();
			$responseList->setObjectIDs($responseIDs);
			$responseList->readObjects();
			$responses = $responseList->getObjects();
			
			foreach ($responses as $response) {
				$commentIDs[] = $response->commentID;
				if ($response->userID) {
					$userIDs[] = $response->userID;
				}
			}
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
		// fetch users
		$users = [];
		$entryIDs = [];
		foreach ($comments as $comment) {
			$entryIDs[] = $comment->objectID;
			if ($comment->userID) {
				$userIDs[] = $comment->userID;
			}
		}
		if (!empty($userIDs)) {
			$users = UserProfileRuntimeCache::getInstance()->getObjects(array_unique($userIDs));
		}
		
		$entrys = [];
		if (!empty($entryIDs)) {
			$entryList = new ViewableEntryList();
			$entryList->setObjectIDs($entryIDs);
			$entryList->readObjects();
			$entrys = $entryList->getObjects();
		}
		
		// set message
		foreach ($likes as $like) {
			if ($like->objectTypeID == $commentLikeObjectType->objectTypeID) {
				// comment like
				if (isset($comments[$like->objectID])) {
					$comment = $comments[$like->objectID];
					
					if (isset($entrys[$comment->objectID]) && $entrys[$comment->objectID]->canRead()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.show.entryComment', [
								'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
								'comment' => $comment,
								'entry' => $entrys[$comment->objectID],
								'like' => $like
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($comment->getExcerpt());
					}
				}
			}
			else {
				// response like
				if (isset($responses[$like->objectID])) {
					$response = $responses[$like->objectID];
					$comment = $comments[$response->commentID];
					
					if (isset($entrys[$comment->objectID]) && $entrys[$comment->objectID]->canRead()) {
						$like->setIsAccessible();
						
						// short output
						$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.uz.show.entryComment.response', [
								'responseAuthor' => $comment->userID ? $users[$response->userID] : null,
								'response' => $response,
								'commentAuthor' => $comment->userID ? $users[$comment->userID] : null,
								'entry' => $entrys[$comment->objectID],
								'like' => $like
						]);
						$like->setTitle($text);
						
						// output
						$like->setDescription($response->getExcerpt());
					}
				}
			}
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function updateCounter($objectID, $value) {
		$entry = new Entry($objectID);
		$editor = new EntryEditor($entry);
		$editor->updateCounters(['comments' => $value]);
	}
}
