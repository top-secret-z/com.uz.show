<?php
namespace show\page;
use show\data\category\ShowCategory;
use show\data\category\ShowCategoryNodeTree;
use show\system\SHOWCore;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Shows the map with all entry locations.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class MapPage extends AbstractPage {
	/**
	 * category
	 */
	public $categoryList = null;
	public $categoryID = 0;
	public $category = null;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['GOOGLE_MAPS_API_KEY', 'SHOW_GEODATA_MAP_ENABLE'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.show.canViewEntry'];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// categorized list
		if (isset($_REQUEST['id'])) {
			$this->categoryID = intval($_REQUEST['id']);
			$this->category = ShowCategory::getCategory($this->categoryID);
			if ($this->category === null) {
				throw new IllegalLinkException();
			}
			if (!$this->category->isAccessible()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// get categories
		$categoryTree = new ShowCategoryNodeTree('com.uz.show.category');
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
		
		SHOWCore::getInstance()->setLocation();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'category' => $this->category,
				'categoryID' => $this->categoryID,
				'categoryList' => $this->categoryList
		]);
	}
}
