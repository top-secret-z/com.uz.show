<?php
namespace show\data\entry;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\edit\IHistorySavingObjectTypeProvider;
use wcf\system\exception\PermissionDeniedException;

/**
 * Object type provider for history saving point entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class HistorySavingEntryProvider extends EntryProvider implements IHistorySavingObjectTypeProvider {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = HistorySavingEntry::class;
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions(IHistorySavingObject $object) {
		if (!($object instanceof HistorySavingEntry)) {
			throw new \InvalidArgumentException("Object is no instance of '".self::class."', instance of '".get_class($object)."' given.");
		}
		
		if (!$object->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getActivePageMenuItem() {
		return '';
	}
}
