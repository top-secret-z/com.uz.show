<?php
namespace show\system\sitemap\object;
use show\data\category\ShowCategory;
use wcf\data\category\CategoryList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObject;
use wcf\system\sitemap\object\AbstractSitemapObjectObjectType;

/**
 * Category sitemap implementation.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategorySitemapObject extends AbstractSitemapObjectObjectType {
	/**
	 * @inheritDoc
	 */
	public function canView(DatabaseObject $object) {
		return $object->isAccessible();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectClass() {
		return ShowCategory::class;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectList() {
		$categoryList = new CategoryList();
		$categoryList->decoratorClassName = $this->getObjectClass();
		$categoryList->getConditionBuilder()->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.uz.show.category')]);
		
		return $categoryList;
	}
}
