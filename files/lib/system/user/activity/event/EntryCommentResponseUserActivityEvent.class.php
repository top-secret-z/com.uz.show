<?php
namespace show\system\user\activity\event;
use show\data\entry\ViewableEntryList;
use wcf\data\comment\response\CommentResponseList;
use wcf\data\comment\CommentList;
use wcf\data\user\UserList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for entry comment responses.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$responseIDs = [];
		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}
		
		// fetch responses
		$responseList = new CommentResponseList();
		$responseList->setObjectIDs($responseIDs);
		$responseList->readObjects();
		$responses = $responseList->getObjects();
		
		// fetch comments
		$commentIDs = $comments = [];
		foreach ($responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		if (!empty($commentIDs)) {
			$commentList = new CommentList();
			$commentList->setObjectIDs($commentIDs);
			$commentList->readObjects();
			$comments = $commentList->getObjects();
		}
		
		// fetch entrys
		$entryIDs = $entrys = [];
		foreach ($comments as $comment) {
			$entryIDs[] = $comment->objectID;
		}
		if (!empty($entryIDs)) {
			$entryList = new ViewableEntryList();
			$entryList->setObjectIDs($entryIDs);
			$entryList->readObjects();
			$entrys = $entryList->getObjects();
		}
		
		// fetch users
		$userIDs = $user = [];
		foreach ($comments as $comment) {
			$userIDs[] = $comment->userID;
		}
		if (!empty($userIDs)) {
			$userList = new UserList();
			$userList->setObjectIDs($userIDs);
			$userList->readObjects();
			$users = $userList->getObjects();
		}
		
		// set message
		foreach ($events as $event) {
			if (isset($responses[$event->objectID])) {
				$response = $responses[$event->objectID];
				$comment = $comments[$response->commentID];
				if (isset($entrys[$comment->objectID]) && isset($users[$comment->userID])) {
					$entry = $entrys[$comment->objectID];
					
					// check permissions
					if (!$entry->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// title
					$text = WCF::getLanguage()->getDynamicVariable('show.entry.recentActivity.entryCommentResponse', [
							'commentAuthor' => $users[$comment->userID],
							'commentID' => $comment->commentID,
							'responseID' => $response->responseID,
							'entry' => $entry
					]);
					$event->setTitle($text);
					
					// description
					$event->setDescription($response->getExcerpt());
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
