<?php
namespace show\page;
use show\data\category\ShowCategory;
use show\data\entry\CategoryEntryList;
use show\system\SHOWCore;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Shows a list of entrys in a certain category.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryEntryListPage extends EntryListPage {
	/**
	 * category the listed entrys belong to
	 */
	public $category;
	public $categoryID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $controllerName = 'CategoryEntryList';
	
	/**
	 * @inheritDoc
	 */
	public $templateName = 'entryList';
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'categoryID' => $this->categoryID,
				'category' => $this->category,
				'controllerObject' => $this->category,
				'feedControllerName' => 'CategoryEntryListFeed'
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		SHOWCore::getInstance()->setLocation($this->category->getParentCategories());
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = ShowCategory::getCategory($this->categoryID);
		if ($this->category === null) {
			throw new IllegalLinkException();
		}
		$this->controllerParameters['object'] = $this->category;
		parent::readParameters();
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('CategoryEntryList', [
				'application' => 'show',
				'object' => $this->category
		], ($this->pageNo > 1 ? 'pageNo=' . $this->pageNo : ''));
		
		$this->labelGroups = $this->category->getLabelGroups('canViewLabel');
	}
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions() {
		parent::checkPermissions();
		
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new CategoryEntryList($this->categoryID, true);
		
		$this->applyFilters();
	}
}
