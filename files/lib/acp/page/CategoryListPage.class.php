<?php
namespace show\acp\page;
use wcf\acp\page\AbstractCategoryListPage;

/**
 * Shows the category list.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryListPage extends AbstractCategoryListPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'show.acp.menu.link.show.category.list';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.show.category';
}
