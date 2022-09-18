<?php
namespace show\data\entry\option;
use show\system\cache\builder\EntryOptionCacheBuilder;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;

/**
 * Provides functions to edit entry options.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = EntryOption::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		EntryOptionCacheBuilder::getInstance()->reset();
	}
}
