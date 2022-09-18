<?php
namespace show\data\entry;

/**
 * Represents a list of show search results.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class SearchResultEntryList extends ViewableEntryList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = SearchResultEntry::class;
}
