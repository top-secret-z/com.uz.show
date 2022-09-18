<?php
namespace show\page;
use show\data\category\ShowCategory;
use show\data\entry\CategoryFeedEntryList;
use wcf\page\AbstractFeedPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Shows entrys for the specified categories in feed.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.show
 */
class CategoryEntryListFeedPage extends EntryListFeedPage {
	/**
	 * category the listed entrys belong to
	 */
	public $category;
	public $categoryID = 0;
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		AbstractFeedPage::readData();
		
		// read the entrys
		$this->items = new CategoryFeedEntryList($this->categoryID, true);
		$this->items->sqlLimit = 20;
		$this->items->readObjects();
		$this->title = $this->category->getTitle();
	}
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->categoryID = intval($_REQUEST['id']);
		$this->category = ShowCategory::getCategory($this->categoryID);
		if ($this->category === null) {
			throw new IllegalLinkException();
		}
		if (!$this->category->isAccessible()) {
			throw new PermissionDeniedException();
		}
	}
}
