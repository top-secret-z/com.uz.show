<?php
namespace show\system\user\notification\object\type;
use show\data\entry\Entry;
use show\data\entry\EntryList;
use show\system\user\notification\object\EntryUserNotificationObject;
use wcf\system\user\notification\object\type\AbstractUserNotificationObjectType;

/**
 * Represents an entry as a notification object type.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryUserNotificationObjectType extends AbstractUserNotificationObjectType {
	/**
	 * @inheritDoc
	 */
	protected static $decoratorClassName = EntryUserNotificationObject::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectClassName = Entry::class;
	
	/**
	 * @inheritDoc
	 */
	protected static $objectListClassName = EntryList::class;
}
