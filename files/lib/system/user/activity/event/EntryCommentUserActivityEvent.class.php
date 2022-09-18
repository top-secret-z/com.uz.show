<?php
namespace show\system\user\activity\event;
use show\data\entry\ViewableEntryList;
use wcf\data\comment\CommentList;
use wcf\system\user\activity\event\IUserActivityEvent;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * User activity event implementation for entry comments.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent {
	/**
	 * @inheritDoc
	 */
	public function prepare(array $events) {
		$commentIDs = [];
		foreach ($events as $event) {
			$commentIDs[] = $event->objectID;
		}
		
		// fetch comments
		$commentList = new CommentList();
		$commentList->setObjectIDs($commentIDs);
		$commentList->readObjects();
		$comments = $commentList->getObjects();
		
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
		
		// set message
		foreach ($events as $event) {
			if (isset($comments[$event->objectID])) {
				$comment = $comments[$event->objectID];
				if (isset($entrys[$comment->objectID])) {
					$entry = $entrys[$comment->objectID];
					
					// check permissions
					if (!$entry->canRead()) {
						continue;
					}
					$event->setIsAccessible();
					
					// add title
					$text = WCF::getLanguage()->getDynamicVariable('show.entry.recentActivity.entryComment', [
							'commentID' => $comment->commentID,
							'entry' => $entry
					]);
					$event->setTitle($text);
					
					// add text
					$event->setDescription($comment->getExcerpt());
					continue;
				}
			}
			
			$event->setIsOrphaned();
		}
	}
}
