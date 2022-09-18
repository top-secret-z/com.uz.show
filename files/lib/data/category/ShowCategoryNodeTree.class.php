<?php
namespace show\data\category;
use wcf\data\category\CategoryNode;
use wcf\data\category\CategoryNodeTree;

/**
 * Represents a list of show category nodes.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryNodeTree extends CategoryNodeTree {
	/**
	 * @inheritDoc
	 */
	protected $nodeClassName = ShowCategoryNode::class;
	
	/**
	 * @inheritDoc
	 */
	public function isIncluded(CategoryNode $categoryNode) {
		return parent::isIncluded($categoryNode) && $categoryNode->isAccessible();
	}
}
