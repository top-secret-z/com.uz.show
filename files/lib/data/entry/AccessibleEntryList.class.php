<?php
namespace show\data\entry;
use show\data\category\ShowCategory;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a list of accessible entrys.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class AccessibleEntryList extends ViewableEntryList {
	protected $applyCategoryFilter = true;
	
	/**
	 * Creates a new AccessibleEntryList object.
	 */
	public function __construct() {
		parent::__construct();
		
		if ($this->applyCategoryFilter) {
			$accessibleCategoryIDs = ShowCategory::getAccessibleCategoryIDs();
			if (!empty($accessibleCategoryIDs)) {
				$this->getConditionBuilder()->add('entry.categoryID IN (?)', [$accessibleCategoryIDs]);
			}
			else {
				$this->getConditionBuilder()->add('1=0');
			}
		}
		
		if (!WCF::getSession()->getPermission('mod.show.canModerateEntry')) {
			if (!WCF::getUser()->userID) {
				$this->getConditionBuilder()->add('entry.isDisabled = 0');
			}
			else {
				$this->getConditionBuilder()->add('(entry.isDisabled = 0 OR entry.userID = ?)', [WCF::getUser()->userID]);
			}
		}
		
		if (!WCF::getSession()->getPermission('mod.show.canViewDeletedEntry')) $this->getConditionBuilder()->add('entry.isDeleted = 0');
		
		// apply language filter
		if (SHOW_ENABLE_MULTILINGUALISM && LanguageFactory::getInstance()->multilingualismEnabled() && count(WCF::getUser()->getLanguageIDs())) {
			$this->getConditionBuilder()->add('(entry.languageID IN (?) OR entry.languageID IS NULL)', [WCF::getUser()->getLanguageIDs()]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		if ($this->objectIDs === null) $this->readObjectIDs();
		
		parent::readObjects();
	}
}
