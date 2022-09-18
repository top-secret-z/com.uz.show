<?php
namespace show\acp\form;
use wcf\acp\form\AbstractCategoryAddForm;

/**
 * Shows the category add form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryAddForm extends AbstractCategoryAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'show.acp.menu.link.show.category.add';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.show.category';
}
