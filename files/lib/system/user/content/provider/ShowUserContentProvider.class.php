<?php
namespace show\system\user\content\provider;
use show\data\entry\Entry;
use wcf\system\user\content\provider\AbstractDatabaseUserContentProvider;

/**
 * User content provider for POIs.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowUserContentProvider extends AbstractDatabaseUserContentProvider {
	/**
	 * @inheritdoc
	 */
	public static function getDatabaseObjectClass() {
		return Entry::class;
	}
}
