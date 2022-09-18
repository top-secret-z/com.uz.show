<?php
namespace show\data;
use wcf\data\DatabaseObject;

/**
 * Abstract class for Show data holder classes.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
abstract class ShowDatabaseObject extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableName() {
		return 'show'.WCF_N.'_'.static::$databaseTableName;
	}
}
