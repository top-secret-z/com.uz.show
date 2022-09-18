<?php
namespace show\data\entry;
use wcf\data\object\type\AbstractObjectTypeProvider;

/**
 * Object type provider implementation for entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntryProvider extends AbstractObjectTypeProvider {
	/**
	 * @inheritDoc
	 */
	public $className = Entry::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = EntryList::class;
}
