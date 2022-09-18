<?php
namespace show\system\option;
use show\data\category\ShowCategoryNodeTree;
use wcf\system\option\AbstractCategoryMultiSelectOptionType;

/**
 * Option type implementation for selecting multiple show categories.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryMultiSelectOptionType extends AbstractCategoryMultiSelectOptionType {
	/**
	 * @inheritDoc
	 */
	public $nodeTreeClassname = ShowCategoryNodeTree::class;
	
	/**
	 * @inheritDoc
	 */
	public $objectType = 'com.uz.show.category';
}
