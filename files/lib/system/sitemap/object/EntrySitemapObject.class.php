<?php
namespace show\system\sitemap\object;
use show\data\entry\Entry;
use wcf\data\DatabaseObject;
use wcf\system\sitemap\object\AbstractSitemapObjectObjectType;

/**
 * Entry sitemap implementation.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class EntrySitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		return $object->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLastModifiedColumn() {
		return 'time';
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return Entry::class;
	}
}
