<?php
namespace show\acp\form;
use wcf\acp\form\AbstractCategoryEditForm;

/**
 * Shows the category edit form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'show.acp.menu.link.show';
	
	/**
	 * @inheritDoc
	 */
	public $objectTypeName = 'com.uz.show.category';
}
