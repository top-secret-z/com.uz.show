<?php
namespace show\system\cache\builder;
use show\data\entry\option\EntryOptionList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * Caches entry options.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryOptionCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$list = new EntryOptionList();
		$list->sqlSelects = "CONCAT('entryOption', CAST(entry_option.optionID AS CHAR)) AS optionName";
		$list->sqlOrderBy = 'showOrder';
		$list->readObjects();
		return $list->getObjects();
	}
}
