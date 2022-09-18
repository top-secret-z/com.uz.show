<?php
namespace show\system\user\notification\object\type;
use wcf\data\comment\response\CommentResponse;
use wcf\data\comment\response\CommentResponseList;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;
use wcf\system\user\notification\object\CommentResponseUserNotificationObject;

/**
 * Represents a comment response notification object type.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryCommentResponseUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = CommentResponseUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = CommentResponse::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = CommentResponseList::class;
}
