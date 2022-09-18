<?php
namespace show\data\entry;

/**
 * Represents a list of entrys for RSS feeds.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryFeedEntryList extends CategoryEntryList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = FeedEntry::class;
}
