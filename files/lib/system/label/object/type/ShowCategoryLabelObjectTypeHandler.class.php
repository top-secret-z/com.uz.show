<?php
namespace show\system\label\object\type;
use show\data\category\ShowCategoryNodeTree;
use show\system\cache\builder\ShowCategoryLabelCacheBuilder;
use wcf\system\label\object\type\AbstractLabelObjectTypeHandler;
use wcf\system\label\object\type\LabelObjectType;
use wcf\system\label\object\type\LabelObjectTypeContainer;

/**
 * Object type handler for show categories.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler {
	/**
	 * category list
	 */
	public $categoryList;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$categoryTree = new ShowCategoryNodeTree('com.uz.show.category');
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		ShowCategoryLabelCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setObjectTypeID($objectTypeID) {
		parent::setObjectTypeID($objectTypeID);
		
		$this->container = new LabelObjectTypeContainer($this->objectTypeID);
		
		foreach ($this->categoryList as $category) {
			$this->container->add(new LabelObjectType($category->getTitle(), $category->categoryID, 0));
			foreach ($category as $subCategory) {
				$this->container->add(new LabelObjectType($subCategory->getTitle(), $subCategory->categoryID, 1));
				foreach ($subCategory as $subSubCategory) {
					$this->container->add(new LabelObjectType($subSubCategory->getTitle(), $subSubCategory->categoryID, 2));
				}
			}
		}
	}
}
