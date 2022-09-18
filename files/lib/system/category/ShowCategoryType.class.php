<?php
namespace show\system\category;
use show\data\entry\EntryAction;
use show\data\entry\EntryList;
use wcf\data\category\CategoryEditor;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type implementation for entry categories.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class ShowCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $forceDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'show.category';
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 3;
	
	/**
	 * @inheritDoc
	 */
	protected $objectTypes = ['com.woltlab.wcf.acl' => 'com.uz.show.category'];
	
	/**
	 * @inheritDoc
	 */
	public function afterDeletion(CategoryEditor $categoryEditor) {
		// delete entrys with no categories
		$entryList = new EntryList();
		$entryList->getConditionBuilder()->add("entry.categoryID IS NULL");
		$entryList->readObjects();
		
		if (count($entryList)) {
			$entryAction = new EntryAction($entryList->getObjects(), 'delete');
			$entryAction->executeAction();
		}
		
		parent::afterDeletion($categoryEditor);
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.show.canManageCategory');
	}
	
	/**
	 * @inheritDoc
	 */
	public function supportsHtmlDescription() {
		return true;
	}
}
