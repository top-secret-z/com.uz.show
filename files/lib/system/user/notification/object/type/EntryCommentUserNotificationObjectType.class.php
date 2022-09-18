<?php
namespace show\system\user\notification\object\type;
use wcf\data\comment\Comment;
use wcf\data\comment\CommentList;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\type\ICommentUserNotificationObjectType;
use wcf\system\user\notification\object\CommentUserNotificationObject;
use wcf\system\WCF;

/**
 * Represents a comment notification object type.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentUserNotificationObjectType extends AbstractUserNotificationObjectType implements ICommentUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Comment::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentList::class;
	
	/**
	 * @inheritDoc
	 */
	public function getOwnerID($objectID) {
		$sql = "SELECT		entry.userID
				FROM		wcf".WCF_N."_comment comment
				LEFT JOIN	show".WCF_N."_entry entry
				ON			(entry.entryID = comment.objectID)
				WHERE		comment.commentID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$objectID]);
		
		return $statement->fetchSingleColumn() ?: 0;
	}
}
